<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CurrencyService;

echo "🧪 Тест CurrencyService\n\n";

$service = app(CurrencyService::class);

// Перевіряємо курси в БД
echo "1️⃣  Курси в БД:\n";
$rates = DB::table('exchange_rates')->get();
foreach ($rates as $rate) {
    echo "   {$rate->base_currency} -> {$rate->target_currency}: {$rate->rate} ({$rate->date})\n";
}
echo "\n";

// Тестуємо конвертацію
echo "2️⃣  Тест конвертації:\n";

try {
    $result = $service->convert(1000, 'USD', 'UAH', new DateTime('2025-10-06'));
    echo "   $1000 USD = {$result} UAH\n";
    
    if ($result == 1000) {
        echo "   ❌ FALLBACK! Має бути ~41,250 UAH\n";
    } else {
        echo "   ✅ Конвертація працює!\n";
    }
} catch (Exception $e) {
    echo "   ❌ Помилка: {$e->getMessage()}\n";
}
echo "\n";

try {
    $result = $service->convert(800, 'PLN', 'UAH', new DateTime('2025-10-02'));
    echo "   800 PLN = {$result} UAH\n";
    
    if ($result == 800) {
        echo "   ❌ FALLBACK! Має бути ~8,400 UAH\n";
    } else {
        echo "   ✅ Конвертація працює!\n";
    }
} catch (Exception $e) {
    echo "   ❌ Помилка: {$e->getMessage()}\n";
}

echo "\n";
echo "3️⃣  Прямий запит до БД:\n";
$usdRate = DB::table('exchange_rates')
    ->where('base_currency', 'USD')
    ->where('target_currency', 'UAH')
    ->where('date', '2025-10-06')
    ->first();

if ($usdRate) {
    echo "   Знайдено: USD -> UAH = {$usdRate->rate}\n";
    echo "   $1000 USD = " . (1000 * $usdRate->rate) . " UAH\n";
} else {
    echo "   ❌ Не знайдено!\n";
}

