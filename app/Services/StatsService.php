<?php

namespace App\Services;

use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsService
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private CurrencyService $currencyService
    ) {}

    /**
     * Отримати загальну статистику за період (з конвертацією валют).
     */
    public function getOverview(int $userId, ?string $fromDate = null, ?string $toDate = null, ?string $currency = null): array
    {
        // Кешування на 5 хвилин
        $cacheKey = "stats_overview_{$userId}_".md5(($fromDate ?? 'null').($toDate ?? 'null').($currency ?? 'null'));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId, $fromDate, $toDate, $currency) {
            return $this->calculateOverview($userId, $fromDate, $toDate, $currency);
        });
    }

    /**
     * Розрахунок статистики (без кешу).
     */
    private function calculateOverview(int $userId, ?string $fromDate, ?string $toDate, ?string $currency = null): array
    {
        // Використовуємо Carbon для коректної обробки дат з часом
        $fromDateCarbon = $fromDate ? Carbon::parse($fromDate)->startOfDay() : Carbon::now()->startOfMonth();
        $toDateCarbon = $toDate ? Carbon::parse($toDate)->endOfDay() : Carbon::now()->endOfDay();

        // Отримуємо базову валюту користувача або використовуємо вибрану
        $user = \App\Models\User::find($userId);
        $baseCurrency = $currency ?? $user->default_currency ?? config('currencies.default', 'UAH');

        // Отримуємо всі транзакції і конвертуємо їх в базову валюту
        $transactions = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->whereBetween('transactions.transaction_date', [$fromDateCarbon->format('Y-m-d H:i:s'), $toDateCarbon->format('Y-m-d H:i:s')])
            ->select('transactions.*', 'categories.type')
            ->get();

        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($transactions as $transaction) {
            $amount = (float) $transaction->amount;

            // Конвертуємо якщо валюта відрізняється
            if ($transaction->currency !== $baseCurrency) {
                try {
                    $amount = $this->currencyService->convert(
                        $amount,
                        $transaction->currency ?? 'UAH',
                        $baseCurrency,
                        new \DateTime($transaction->transaction_date)
                    );
                } catch (\Exception $e) {
                    // Якщо конвертація не вдалась - використовуємо оригінальну суму
                    \Log::warning("Помилка конвертації для транзакції #{$transaction->id}: ".$e->getMessage());
                }
            }

            if ($transaction->type === 'income') {
                $totalIncome += $amount;
            } else {
                $totalExpense += $amount;
            }
        }

        $balance = $totalIncome - $totalExpense;

        // Кількість транзакцій
        $transactionsCount = DB::table('transactions')
            ->where('user_id', $userId)
            ->whereBetween('transaction_date', [$fromDateCarbon->format('Y-m-d H:i:s'), $toDateCarbon->format('Y-m-d H:i:s')])
            ->count();

        // Топ категорій витрат (з конвертацією валют)
        $expensesByCategory = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('categories.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$fromDateCarbon->format('Y-m-d H:i:s'), $toDateCarbon->format('Y-m-d H:i:s')])
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                'categories.color as category_color',
                'transactions.id',
                'transactions.amount',
                'transactions.currency',
                'transactions.transaction_date'
            )
            ->get();

        // Групуємо та конвертуємо
        $categoryTotals = [];
        foreach ($expensesByCategory as $expense) {
            $amount = (float) $expense->amount;

            if ($expense->currency !== $baseCurrency) {
                try {
                    $amount = $this->currencyService->convert(
                        $amount,
                        $expense->currency ?? 'UAH',
                        $baseCurrency,
                        new \DateTime($expense->transaction_date)
                    );
                } catch (\Exception $e) {
                    \Log::warning('Помилка конвертації для категорії: '.$e->getMessage());
                }
            }

            if (! isset($categoryTotals[$expense->category_id])) {
                $categoryTotals[$expense->category_id] = [
                    'category_name' => $expense->category_name,
                    'category_color' => $expense->category_color ?? '#6B7280',
                    'total' => 0,
                ];
            }

            $categoryTotals[$expense->category_id]['total'] += $amount;
        }

        // Сортуємо та обираємо топ-5
        usort($categoryTotals, fn ($a, $b) => $b['total'] <=> $a['total']);
        $topCategories = array_slice($categoryTotals, 0, 5);

        // Додаємо відсотки
        $topCategories = array_map(function ($item) use ($totalExpense) {
            $item['percentage'] = $totalExpense > 0 ? round(($item['total'] / $totalExpense) * 100, 2) : 0;

            return $item;
        }, $topCategories);

        return [
            'period' => [
                'from' => $fromDateCarbon->format('Y-m-d'),
                'to' => $toDateCarbon->format('Y-m-d'),
            ],
            'currency' => $baseCurrency,
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'balance' => (float) $balance,
            'transactions_count' => $transactionsCount,
            'top_expense_categories' => $topCategories,
        ];
    }

    /**
     * Отримати cashflow за періодами (дні, тижні або місяці).
     *
     * @param  int  $userId  ID користувача
     * @param  string  $period  Період: '7d', '14d', '30d', '3m', '6m'
     * @param  string|null  $targetCurrency  Цільова валюта (UAH, USD, PLN, EUR). Якщо null - використовується базова валюта користувача
     */
    public function getCashflow(int $userId, string $period = '6m', ?string $targetCurrency = null): array
    {
        // Отримуємо базову валюту користувача
        $user = \App\Models\User::find($userId);
        $baseCurrency = $targetCurrency ?? $user->default_currency ?? config('currencies.default', 'UAH');

        // Кешування на 5 хвилин
        $cacheKey = "stats_cashflow_{$userId}_{$period}_{$baseCurrency}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId, $period, $baseCurrency) {
            return $this->calculateCashflow($userId, $period, $baseCurrency);
        });
    }

    /**
     * Розрахунок cashflow (без кешу).
     */
    private function calculateCashflow(int $userId, string $period, string $baseCurrency): array
    {

        // Визначаємо період та групування
        $periodConfig = $this->parsePeriod($period);
        $startDate = $periodConfig['start'];
        $endDate = Carbon::now()->endOfDay(); // Включаємо весь поточний день
        $groupBy = $periodConfig['groupBy'];
        $intervals = $periodConfig['intervals'];

        // Використовуємо strftime для SQLite та DATE_FORMAT для MySQL
        $driver = DB::connection()->getDriverName();

        if ($groupBy === 'day') {
            $dateFormat = $driver === 'sqlite'
                ? "strftime('%Y-%m-%d', transactions.transaction_date)"
                : "DATE_FORMAT(transactions.transaction_date, '%Y-%m-%d')";
        } else {
            $dateFormat = $driver === 'sqlite'
                ? "strftime('%Y-%m', transactions.transaction_date)"
                : "DATE_FORMAT(transactions.transaction_date, '%Y-%m')";
        }

        // Отримуємо всі транзакції з валютою
        $transactions = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->whereBetween('transactions.transaction_date', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
            ->select(
                DB::raw("{$dateFormat} as period_key"),
                'categories.type',
                'transactions.amount',
                'transactions.currency',
                'transactions.transaction_date'
            )
            ->orderBy('period_key')
            ->get();

        // Підготувати дані по періодах
        $cashflowData = [];

        foreach ($intervals as $interval) {
            $cashflowData[$interval['key']] = [
                'period' => $interval['label'],
                'income' => 0,
                'expense' => 0,
            ];
        }

        // Заповнити дані з бази з конвертацією валют
        foreach ($transactions as $transaction) {
            if (isset($cashflowData[$transaction->period_key])) {
                $amount = (float) $transaction->amount;

                // Конвертуємо якщо валюта відрізняється
                if ($transaction->currency !== $baseCurrency) {
                    try {
                        $amount = $this->currencyService->convert(
                            $amount,
                            $transaction->currency ?? 'UAH',
                            $baseCurrency,
                            new \DateTime($transaction->transaction_date)
                        );
                    } catch (\Exception $e) {
                        \Log::warning('Помилка конвертації для cashflow: '.$e->getMessage());
                    }
                }

                if ($transaction->type === 'income') {
                    $cashflowData[$transaction->period_key]['income'] += $amount;
                } else {
                    $cashflowData[$transaction->period_key]['expense'] += $amount;
                }
            }
        }

        return [
            'data' => array_values($cashflowData),
            'currency' => $baseCurrency,
        ];
    }

    /**
     * Парсити період та визначити параметри запиту.
     */
    private function parsePeriod(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            '7d' => [
                'start' => $now->copy()->subDays(6)->startOfDay(),
                'groupBy' => 'day',
                'intervals' => $this->generateDayIntervals(7),
            ],
            '14d' => [
                'start' => $now->copy()->subDays(13)->startOfDay(),
                'groupBy' => 'day',
                'intervals' => $this->generateDayIntervals(14),
            ],
            '30d' => [
                'start' => $now->copy()->subDays(29)->startOfDay(),
                'groupBy' => 'day',
                'intervals' => $this->generateDayIntervals(30),
            ],
            '3m' => [
                'start' => $now->copy()->subMonths(3)->startOfMonth(),
                'groupBy' => 'month',
                'intervals' => $this->generateMonthIntervals(3),
            ],
            '6m' => [
                'start' => $now->copy()->subMonths(6)->startOfMonth(),
                'groupBy' => 'month',
                'intervals' => $this->generateMonthIntervals(6),
            ],
            default => [
                'start' => $now->copy()->subMonths(6)->startOfMonth(),
                'groupBy' => 'month',
                'intervals' => $this->generateMonthIntervals(6),
            ],
        };
    }

    /**
     * Генерувати інтервали по днях.
     */
    private function generateDayIntervals(int $days): array
    {
        $intervals = [];
        $now = Carbon::now();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $intervals[] = [
                'key' => $date->format('Y-m-d'),
                'label' => $date->locale('uk')->isoFormat('DD MMM'),
            ];
        }

        return $intervals;
    }

    /**
     * Генерувати інтервали по місяцях.
     */
    private function generateMonthIntervals(int $months): array
    {
        $intervals = [];
        $now = Carbon::now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $intervals[] = [
                'key' => $date->format('Y-m'),
                'label' => $date->locale('uk')->isoFormat('MMM YYYY'),
            ];
        }

        return $intervals;
    }

    /**
     * Отримати розподіл витрат за категоріями (для pie chart).
     */
    public function getCategoryBreakdown(int $userId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $fromDate = $fromDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $toDate = $toDate ?? Carbon::now()->format('Y-m-d');

        // Кешування на 5 хвилин
        $cacheKey = "stats_category_breakdown_{$userId}_".md5($fromDate.$toDate);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId, $fromDate, $toDate) {
            return $this->calculateCategoryBreakdown($userId, $fromDate, $toDate);
        });
    }

    /**
     * Розрахунок розподілу за категоріями (без кешу).
     */
    private function calculateCategoryBreakdown(int $userId, string $fromDate, string $toDate): array
    {

        $totalExpense = $this->transactionRepository->getTotalAmount($userId, 'expense', $fromDate, $toDate);

        $data = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('categories.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$fromDate, $toDate])
            ->select(
                'categories.name as category_name',
                'categories.color as category_color',
                DB::raw('SUM(transactions.amount) as total')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) use ($totalExpense) {
                return [
                    'category_name' => $item->category_name,
                    'category_color' => $item->category_color ?? '#6B7280',
                    'total' => (float) $item->total,
                    'percentage' => $totalExpense > 0 ? round(($item->total / $totalExpense) * 100, 2) : 0,
                ];
            });

        return $data->toArray();
    }
}
