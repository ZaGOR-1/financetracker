<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Services\CurrencyService;

echo "🧪 Тестування мультивалютності\n\n";

$currencyService = app(CurrencyService::class);

// 1. Тест форматування
echo "1️⃣  Форматування сум:\n";
echo '   UAH: '.$currencyService->format(1500.50, 'UAH')."\n";
echo '   USD: '.$currencyService->format(100, 'USD')."\n";
echo '   PLN: '.$currencyService->format(250.75, 'PLN')."\n\n";

// 2. Тест конвертації
echo "2️⃣  Конвертація валют (курси НБУ):\n";
try {
    $usdToUah = $currencyService->convert(100, 'USD', 'UAH');
    echo '   $100 USD = '.number_format($usdToUah, 2)." UAH\n";

    $plnToUah = $currencyService->convert(100, 'PLN', 'UAH');
    echo '   100 PLN = '.number_format($plnToUah, 2)." UAH\n";

    $usdToPln = $currencyService->convert(100, 'USD', 'PLN');
    echo '   $100 USD = '.number_format($usdToPln, 2)." PLN\n\n";
} catch (Exception $e) {
    echo '   ❌ Помилка: '.$e->getMessage()."\n\n";
}

// 3. Перевірка транзакцій
echo "3️⃣  Транзакції в різних валютах:\n";
$transactions = Transaction::with('category')->take(5)->get();

foreach ($transactions as $t) {
    echo "   • {$t->description}: {$t->formatted_amount} ({$t->category->name})\n";
}
echo "\n";

// 4. Перевірка курсів у БД
echo "4️⃣  Курси у базі даних:\n";
$rates = DB::table('exchange_rates')
    ->orderBy('date', 'desc')
    ->take(6)
    ->get();

if ($rates->isEmpty()) {
    echo "   ⚠️  Курси не знайдені. Запустіть: php artisan currency:update-rates\n\n";
} else {
    foreach ($rates as $rate) {
        echo "   {$rate->base_currency} → {$rate->target_currency}: ".number_format($rate->rate, 6)." ({$rate->date})\n";
    }
    echo "\n";
}

// 5. Статистика
echo "5️⃣  Загальна статистика:\n";
$totalsByCurrency = DB::table('transactions')
    ->select('currency', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
    ->groupBy('currency')
    ->get();

foreach ($totalsByCurrency as $stat) {
    $symbol = $currencyService->getSymbol($stat->currency ?? 'UAH');
    echo "   {$stat->currency}: {$stat->count} транзакцій на суму {$symbol}".number_format($stat->total, 2)."\n";
}

echo "\n✅ Тестування завершено!\n";
echo "\n📖 Детальна документація: docs/multi-currency-guide.md\n";
