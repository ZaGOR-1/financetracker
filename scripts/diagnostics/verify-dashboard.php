<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\StatsService;

echo "📊 Перевірка розрахунків доходів/витрат\n";
echo str_repeat('=', 70)."\n\n";

$statsService = app(StatsService::class);

// Отримуємо ID користувача (припускаємо 1)
$userId = 1;
$user = DB::table('users')->find($userId);

if (! $user) {
    echo "❌ Користувача не знайдено!\n";
    exit(1);
}

echo "👤 Користувач: {$user->name}\n";
echo "💱 Базова валюта: {$user->default_currency}\n\n";

// Отримуємо всі транзакції
$transactions = DB::table('transactions')
    ->where('user_id', $userId)
    ->orderBy('transaction_date')
    ->get();

echo "📋 Транзакції:\n";
echo str_repeat('-', 70)."\n";

$totalIncome = 0;
$totalExpense = 0;

foreach ($transactions as $transaction) {
    $category = DB::table('categories')->find($transaction->category_id);
    $type = $category->type;
    $typeIcon = $type === 'income' ? '📈' : '📉';

    echo sprintf(
        "%s %s | %s %s | %s | %s\n",
        $typeIcon,
        date('Y-m-d H:i', strtotime($transaction->transaction_date)),
        number_format($transaction->amount, 2),
        $transaction->currency,
        $category->name,
        $transaction->description
    );

    // Конвертуємо в базову валюту
    $currencyService = app(\App\Services\CurrencyService::class);
    $convertedAmount = $currencyService->convert(
        $transaction->amount,
        $transaction->currency,
        $user->default_currency,
        new DateTime($transaction->transaction_date)
    );

    if ($type === 'income') {
        $totalIncome += $convertedAmount;
        echo sprintf("   → Дохід: %.2f %s\n", $convertedAmount, $user->default_currency);
    } else {
        $totalExpense += $convertedAmount;
        echo sprintf("   → Витрата: %.2f %s\n", $convertedAmount, $user->default_currency);
    }
}

echo "\n".str_repeat('=', 70)."\n";
echo sprintf("💰 Загальний дохід:    %s\n", number_format($totalIncome, 2).' '.$user->default_currency);
echo sprintf("💸 Загальні витрати:   %s\n", number_format($totalExpense, 2).' '.$user->default_currency);
echo sprintf("📊 Баланс:             %s\n", number_format($totalIncome - $totalExpense, 2).' '.$user->default_currency);

// Порівнюємо з API
echo "\n".str_repeat('-', 70)."\n";
echo "🔍 Перевірка через StatsService:\n\n";

$stats = $statsService->getOverview($userId, '2025-10-01', '2025-10-31');

echo sprintf("API Дохід:    %s\n", number_format($stats['total_income'], 2).' '.$stats['currency']);
echo sprintf("API Витрати:  %s\n", number_format($stats['total_expense'], 2).' '.$stats['currency']);
echo sprintf("API Баланс:   %s\n", number_format($stats['balance'], 2).' '.$stats['currency']);

echo "\n";

if (abs($totalIncome - $stats['total_income']) < 0.01) {
    echo "✅ Доходи збігаються!\n";
} else {
    echo '❌ Доходи НЕ збігаються! Різниця: '.($totalIncome - $stats['total_income'])."\n";
}

if (abs($totalExpense - $stats['total_expense']) < 0.01) {
    echo "✅ Витрати збігаються!\n";
} else {
    echo '❌ Витрати НЕ збігаються! Різниця: '.($totalExpense - $stats['total_expense'])."\n";
}

echo "\n✅ Перевірка завершена!\n";
