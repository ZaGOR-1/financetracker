<?php

require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\User;
use App\Services\CurrencyService;

echo "🔍 Діагностика проблеми з транзакцією $1000\n\n";

// 1. Перевіряємо останню транзакцію
echo "1️⃣  Остання транзакція:\n";
$lastTransaction = Transaction::with('category')->latest()->first();

if ($lastTransaction) {
    echo "   ID: {$lastTransaction->id}\n";
    echo "   Сума: {$lastTransaction->amount} {$lastTransaction->currency}\n";
    echo "   Категорія: {$lastTransaction->category->name} ({$lastTransaction->category->type})\n";
    echo "   Дата: {$lastTransaction->transaction_date}\n";
    echo "   Користувач: {$lastTransaction->user_id}\n\n";
} else {
    echo "   ❌ Транзакцій немає!\n\n";
    exit;
}

// 2. Перевіряємо всі транзакції користувача
$user = User::find($lastTransaction->user_id);
echo "2️⃣  Користувач: {$user->name}\n";
echo "   Базова валюта: {$user->default_currency}\n\n";

// 3. Перевіряємо курс USD->UAH
echo "3️⃣  Курс валют:\n";
$currencyService = app(CurrencyService::class);

try {
    $rate = DB::table('exchange_rates')
        ->where('base_currency', 'USD')
        ->where('target_currency', 'UAH')
        ->where('date', now()->format('Y-m-d'))
        ->first();
    
    if ($rate) {
        echo "   USD -> UAH: {$rate->rate} (дата: {$rate->date})\n";
        $convertedAmount = 1000 * $rate->rate;
        echo "   $1000 USD = " . number_format($convertedAmount, 2) . " UAH\n\n";
    } else {
        echo "   ⚠️  Курс USD->UAH не знайдено в БД!\n";
        echo "   Спробую отримати з API НБУ...\n";
        
        $converted = $currencyService->convert(1000, 'USD', 'UAH');
        echo "   $1000 USD = " . number_format($converted, 2) . " UAH\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Помилка: {$e->getMessage()}\n\n";
}

// 4. Перевіряємо статистику через StatsService
echo "4️⃣  Статистика дашборда:\n";
$statsService = app(\App\Services\StatsService::class);

try {
    $stats = $statsService->getOverview($user->id);
    
    echo "   Валюта дашборда: {$stats['currency']}\n";
    echo "   Доходи: " . number_format($stats['total_income'], 2) . " {$stats['currency']}\n";
    echo "   Витрати: " . number_format($stats['total_expense'], 2) . " {$stats['currency']}\n";
    echo "   Баланс: " . number_format($stats['balance'], 2) . " {$stats['currency']}\n";
    echo "   Кількість транзакцій: {$stats['transactions_count']}\n\n";
} catch (Exception $e) {
    echo "   ❌ Помилка StatsService: {$e->getMessage()}\n\n";
}

// 5. Перевіряємо всі доходи в USD
echo "5️⃣  Всі доходи в USD:\n";
$usdIncomes = Transaction::whereHas('category', function($q) {
    $q->where('type', 'income');
})
->where('currency', 'USD')
->where('user_id', $user->id)
->get();

if ($usdIncomes->count() > 0) {
    $totalUsd = 0;
    foreach ($usdIncomes as $income) {
        echo "   • {$income->description}: \${$income->amount} ({$income->transaction_date->format('d.m.Y H:i')})\n";
        $totalUsd += $income->amount;
    }
    echo "   Всього: \${$totalUsd}\n\n";
} else {
    echo "   ⚠️  Немає доходів в USD!\n\n";
}

// 6. Рекомендації
echo "6️⃣  Рекомендації:\n";

if (!$rate) {
    echo "   ⚠️  ПРОБЛЕМА: Курси валют не оновлені!\n";
    echo "   РІШЕННЯ: Запустіть команду:\n";
    echo "      php update-currency-rates.php\n\n";
}

if ($lastTransaction->currency === 'USD' && $lastTransaction->category->type === 'income') {
    echo "   ✅ Транзакція створена коректно\n";
    echo "   💡 Якщо дашборд не оновився - оновіть сторінку (F5 або Ctrl+R)\n";
}

echo "\n🔄 Після оновлення курсів перезавантажте дашборд!\n";

