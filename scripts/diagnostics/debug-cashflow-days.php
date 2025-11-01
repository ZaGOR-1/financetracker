<?php

/**
 * Діагностика Cashflow по днях
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\StatsService;

echo "═══════════════════════════════════════════════════\n";
echo "🔍 Діагностика Cashflow по днях (7d)\n";
echo "═══════════════════════════════════════════════════\n\n";

$user = User::first();

if (!$user) {
    echo "❌ Користувача не знайдено!\n";
    exit(1);
}

echo "👤 Користувач: {$user->name}\n";
echo "💰 Базова валюта: {$user->default_currency}\n\n";

$statsService = app(StatsService::class);

echo "📊 Тестування періоду: 7 днів\n";
echo "═══════════════════════════════════════════════════\n\n";

$result = $statsService->getCashflow($user->id, '7d', 'UAH');

echo "💱 Валюта відповіді: {$result['currency']}\n";
echo "📈 Кількість періодів: " . count($result['data']) . "\n\n";

echo "Детальні дані:\n";
echo "─────────────────────────────────────────────────\n";

foreach ($result['data'] as $index => $period) {
    $income = number_format($period['income'], 2, '.', ',');
    $expense = number_format($period['expense'], 2, '.', ',');
    $balance = number_format($period['income'] - $period['expense'], 2, '.', ',');
    
    echo sprintf(
        "%d. %s | 📈 %s ₴ | 📉 %s ₴ | 💰 %s ₴\n",
        $index + 1,
        $period['period'],
        $income,
        $expense,
        $balance
    );
}

echo "\n═══════════════════════════════════════════════════\n";
echo "📋 Транзакції в базі за останні 7 днів:\n";
echo "═══════════════════════════════════════════════════\n\n";

$transactions = \App\Models\Transaction::where('user_id', $user->id)
    ->where('transaction_date', '>=', now()->subDays(6)->startOfDay())
    ->orderBy('transaction_date', 'desc')
    ->with('category')
    ->get();

if ($transactions->count() === 0) {
    echo "❌ Немає транзакцій за останні 7 днів!\n";
} else {
    foreach ($transactions as $trans) {
        $type = $trans->category->type === 'income' ? '📈' : '📉';
        $typeText = $trans->category->type === 'income' ? 'Дохід' : 'Витрата';
        
        echo sprintf(
            "%s %s | %s %s | %s | %s\n",
            $type,
            $trans->transaction_date->format('Y-m-d H:i'),
            $trans->amount,
            $trans->currency,
            $typeText,
            $trans->category->name
        );
    }
    
    echo "\n✅ Всього транзакцій: " . $transactions->count() . "\n";
}

echo "\n═══════════════════════════════════════════════════\n";
echo "🔍 Перевірка генерації інтервалів:\n";
echo "═══════════════════════════════════════════════════\n\n";

$now = \Carbon\Carbon::now();
echo "Поточна дата: " . $now->format('Y-m-d H:i:s') . "\n";
echo "Початок періоду: " . $now->copy()->subDays(6)->startOfDay()->format('Y-m-d H:i:s') . "\n\n";

echo "Генерація ключів для 7 днів:\n";
for ($i = 6; $i >= 0; $i--) {
    $date = $now->copy()->subDays($i);
    $key = $date->format('Y-m-d');
    $label = $date->locale('uk')->isoFormat('DD MMM');
    
    echo sprintf("  %s → %s\n", $key, $label);
}

echo "\n═══════════════════════════════════════════════════\n";
echo "✅ Діагностика завершена!\n";
echo "═══════════════════════════════════════════════════\n";
