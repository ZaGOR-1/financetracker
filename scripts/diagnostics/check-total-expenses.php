<?php

/**
 * Перевірка загальної суми витрат
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Transaction;

echo "═══════════════════════════════════════════════════\n";
echo "💸 Перевірка витрат\n";
echo "═══════════════════════════════════════════════════\n\n";

$user = User::first();

if (!$user) {
    echo "❌ Користувача не знайдено!\n";
    exit(1);
}

echo "👤 Користувач: {$user->name}\n\n";

$expenses = Transaction::where('user_id', $user->id)
    ->whereHas('category', function($q) {
        $q->where('type', 'expense');
    })
    ->with('category')
    ->orderBy('transaction_date', 'desc')
    ->get();

echo "💰 Всього транзакцій-витрат: {$expenses->count()}\n";
echo "💸 Список всіх витрат:\n\n";

$totalUAH = 0;
$totalUSD = 0;
$totalPLN = 0;

foreach($expenses as $e) {
    echo sprintf(
        "%s | %10s %s | %s\n",
        $e->transaction_date->format('Y-m-d H:i'),
        number_format($e->amount, 2, '.', ','),
        $e->currency,
        $e->category->name
    );
    
    if ($e->currency === 'UAH') {
        $totalUAH += $e->amount;
    } elseif ($e->currency === 'USD') {
        $totalUSD += $e->amount;
    } elseif ($e->currency === 'PLN') {
        $totalPLN += $e->amount;
    }
}

echo "\n═══════════════════════════════════════════════════\n";
echo "📊 Підсумки по валютах:\n";
echo "═══════════════════════════════════════════════════\n\n";
echo "UAH: " . number_format($totalUAH, 2, '.', ',') . " ₴\n";
echo "USD: " . number_format($totalUSD, 2, '.', ',') . " $\n";
echo "PLN: " . number_format($totalPLN, 2, '.', ',') . " zł\n";

echo "\n═══════════════════════════════════════════════════\n";
echo "📈 Перевірка DashboardStats (6 місяців):\n";
echo "═══════════════════════════════════════════════════\n\n";

$statsService = app(\App\Services\StatsService::class);
$overview = $statsService->getOverview($user->id);

echo "Період: {$overview['period']['from']} - {$overview['period']['to']}\n";
echo "Витрати на Dashboard: " . number_format($overview['total_expense'], 2, '.', ',') . " {$overview['currency']}\n";
echo "Доходи на Dashboard: " . number_format($overview['total_income'], 2, '.', ',') . " {$overview['currency']}\n";
echo "Баланс: " . number_format($overview['balance'], 2, '.', ',') . " {$overview['currency']}\n";
echo "Кількість транзакцій: {$overview['transactions_count']}\n";

echo "\n═══════════════════════════════════════════════════\n";
echo "✅ Перевірка завершена!\n";
echo "═══════════════════════════════════════════════════\n";
