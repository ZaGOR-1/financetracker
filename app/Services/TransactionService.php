<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionService
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository,
        private CacheService $cacheService
    ) {}

    /**
     * Отримати транзакції користувача з пагінацією (без кешування пагінації).
     */
    public function getUserTransactions(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        // Пагінація не кешується, бо динамічна
        return $this->transactionRepository->getUserTransactions($userId, $filters, $perPage);
    }

    /**
     * Отримати транзакцію за ID.
     */
    public function getTransactionById(int $transactionId, int $userId): Transaction
    {
        $transaction = Transaction::with('category')->findOrFail($transactionId);

        if ($transaction->user_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        return $transaction;
    }

    /**
     * Отримати загальну суму транзакцій (з кешуванням).
     */
    public function getTotalAmount(int $userId, string $type, ?string $startDate = null, ?string $endDate = null): float
    {
        $cacheKey = $this->cacheService->statsKey($userId, $startDate, $endDate, ['type' => $type, 'total' => true]);

        return $this->cacheService->remember(
            'stats',
            $cacheKey,
            fn () => $this->transactionRepository->getTotalAmount($userId, $type, $startDate, $endDate),
            10 // 10 хвилин
        );
    }

    /**
     * Створити нову транзакцію.
     */
    public function createTransaction(int $userId, array $data): Transaction
    {
        \Log::channel('transactions')->info('Creating transaction', [
            'user_id' => $userId,
            'type' => $data['type'] ?? 'unknown',
            'amount' => $data['amount'] ?? null,
        ]);

        $data['user_id'] = $userId;

        // Валідація: amount має бути позитивним
        if ($data['amount'] <= 0) {
            \Log::channel('errors')->error('Invalid transaction amount', [
                'user_id' => $userId,
                'amount' => $data['amount'],
            ]);
            throw new \Exception('Amount must be greater than zero');
        }

        // Валідація: дата не може бути у майбутньому
        if (isset($data['transaction_date']) && strtotime($data['transaction_date']) > time()) {
            \Log::channel('errors')->error('Future transaction date', [
                'user_id' => $userId,
                'date' => $data['transaction_date'],
            ]);
            throw new \Exception('Transaction date cannot be in the future');
        }

        // Встановлюємо тип транзакції на основі категорії
        if (! isset($data['type']) && isset($data['category_id'])) {
            $category = \App\Models\Category::findOrFail($data['category_id']);
            $data['type'] = $category->type;
        }

        $transaction = $this->transactionRepository->create($data);

        \Log::channel('transactions')->info('Transaction created successfully', [
            'user_id' => $userId,
            'transaction_id' => $transaction->id,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
        ]);

        // Очищаємо кеш транзакцій та статистики користувача
        $this->cacheService->forgetUserTransactions($userId);
        $this->clearStatsCache($userId);

        return $transaction;
    }

    /**
     * Оновити транзакцію.
     */
    public function updateTransaction(int $userId, int $transactionId, array $data): Transaction
    {
        $transaction = $this->transactionRepository->find($transactionId);

        if (! $transaction) {
            throw new \Exception('Transaction not found');
        }

        // Перевірка права власності
        if ($transaction->user_id !== $userId) {
            throw new \Exception('Unauthorized');
        }

        // Валідація
        if (isset($data['amount']) && $data['amount'] <= 0) {
            throw new \Exception('Amount must be greater than zero');
        }

        if (isset($data['transaction_date']) && strtotime($data['transaction_date']) > time()) {
            throw new \Exception('Transaction date cannot be in the future');
        }

        // Якщо змінюється категорія, оновлюємо тип
        if (isset($data['category_id']) && $data['category_id'] !== $transaction->category_id) {
            $category = \App\Models\Category::findOrFail($data['category_id']);
            $data['type'] = $category->type;
        }

        $updated = $this->transactionRepository->update($transactionId, $data);

        // Очищаємо кеш транзакцій та статистики користувача
        $this->cacheService->forgetUserTransactions($userId);
        $this->clearStatsCache($userId);

        return $updated;
    }

    /**
     * Видалити транзакцію.
     */
    public function deleteTransaction(int $userId, int $transactionId): bool
    {
        $transaction = $this->transactionRepository->find($transactionId);

        if (! $transaction) {
            throw new \Exception('Transaction not found');
        }

        // Перевірка права власності
        if ($transaction->user_id !== $userId) {
            throw new \Exception('Unauthorized');
        }

        $deleted = $this->transactionRepository->delete($transactionId);

        // Очищаємо кеш транзакцій та статистики користувача
        if ($deleted) {
            $this->cacheService->forgetUserTransactions($userId);
            $this->clearStatsCache($userId);
        }

        return $deleted;
    }

    /**
     * Очистити кеш статистики користувача.
     *
     * StatsService використовує власні ключі кешу, тому очищаємо їх безпосередньо.
     */
    private function clearStatsCache(int $userId): void
    {
        // Очищаємо кеш статистики (overview, cashflow, category breakdown)
        // Використовуємо паттерн ключів з StatsService
        \Illuminate\Support\Facades\Cache::forget("stats_overview_{$userId}_*");
        \Illuminate\Support\Facades\Cache::forget("stats_cashflow_{$userId}_*");
        \Illuminate\Support\Facades\Cache::forget("stats_category_breakdown_{$userId}_*");

        // Для надійності очищаємо всі можливі комбінації ключів
        // (оскільки ключі містять хеші параметрів)
        $cacheDriver = config('cache.default');

        if ($cacheDriver === 'file') {
            // Для file cache просто очищаємо всі відомі ключі
            $periods = ['7d', '14d', '30d', '3m', '6m'];
            $currencies = ['UAH', 'USD', 'PLN', 'EUR'];

            foreach ($periods as $period) {
                foreach ($currencies as $currency) {
                    \Illuminate\Support\Facades\Cache::forget("stats_cashflow_{$userId}_{$period}_{$currency}");
                }
            }

            // Очищаємо overview для поточного місяця та інших можливих періодів
            $now = \Carbon\Carbon::now();
            $dates = [
                ['from' => $now->copy()->startOfMonth()->format('Y-m-d'), 'to' => $now->copy()->endOfMonth()->format('Y-m-d')],
                ['from' => $now->copy()->subMonth()->startOfMonth()->format('Y-m-d'), 'to' => $now->copy()->subMonth()->endOfMonth()->format('Y-m-d')],
                ['from' => $now->copy()->startOfYear()->format('Y-m-d'), 'to' => $now->copy()->endOfYear()->format('Y-m-d')],
            ];

            foreach ($dates as $dateRange) {
                $hash = md5($dateRange['from'].$dateRange['to']);
                \Illuminate\Support\Facades\Cache::forget("stats_overview_{$userId}_{$hash}");
                \Illuminate\Support\Facades\Cache::forget("stats_category_breakdown_{$userId}_{$hash}");
            }
        }

        // Також очищаємо кеш для дефолтних параметрів (null dates)
        $defaultHash = md5('nullnull');
        \Illuminate\Support\Facades\Cache::forget("stats_overview_{$userId}_{$defaultHash}");
        \Illuminate\Support\Facades\Cache::forget("stats_category_breakdown_{$userId}_{$defaultHash}");
    }
}
