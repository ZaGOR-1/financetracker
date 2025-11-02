<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Services\CurrencyService;

echo "🔍 Детальний розрахунок доходів\n\n";

$user = \App\Models\User::first();
$baseCurrency = $user->default_currency ?? 'UAH';
$currencyService = app(CurrencyService::class);

echo "Базова валюта: {$baseCurrency}\n\n";

// Отримуємо всі доходи поточного місяця
$incomes = Transaction::whereHas('category', function ($q) {
    $q->where('type', 'income');
})
    ->where('user_id', $user->id)
    ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
    ->with('category')
    ->get();

echo "📊 Доходи за {$incomes->first()->transaction_date->format('F Y')}:\n\n";

$totalInBaseCurrency = 0;

foreach ($incomes as $income) {
    $originalAmount = $income->amount;
    $originalCurrency = $income->currency ?? 'UAH';

    // Конвертуємо
    if ($originalCurrency !== $baseCurrency) {
        try {
            $convertedAmount = $currencyService->convert(
                $originalAmount,
                $originalCurrency,
                $baseCurrency,
                $income->transaction_date
            );
        } catch (Exception $e) {
            echo "   ❌ Помилка конвертації: {$e->getMessage()}\n";
            $convertedAmount = $originalAmount; // fallback
        }
    } else {
        $convertedAmount = $originalAmount;
    }

    $totalInBaseCurrency += $convertedAmount;

    echo sprintf(
        "   %s: %s %s = %s %s\n",
        $income->category->name,
        number_format($originalAmount, 2),
        $originalCurrency,
        number_format($convertedAmount, 2),
        $baseCurrency
    );
}

echo "\n".str_repeat('=', 50)."\n";
echo sprintf("   ВСЬОГО: %s %s\n", number_format($totalInBaseCurrency, 2), $baseCurrency);
echo str_repeat('=', 50)."\n\n";

echo "💡 Якщо ця сума відрізняється від дашборда - є баг у StatsService\n";
