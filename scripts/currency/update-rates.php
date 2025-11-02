<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\CurrencyService;

echo "🔄 Оновлення курсів валют через ExchangeRate-API\n";
echo str_repeat('=', 70)."\n\n";

$service = app(CurrencyService::class);

// Отримуємо список валют
$currencies = array_keys($service->getSupportedCurrencies());

echo '💱 Валюти: '.implode(', ', $currencies)."\n\n";

// Оновлюємо всі курси
echo "📡 Отримання курсів...\n";
echo str_repeat('-', 70)."\n";

$results = $service->updateAllRates();

foreach ($results as $pair => $result) {
    if ($result['success']) {
        echo sprintf("✅ %-15s = %10.6f\n", $pair, $result['rate']);
    } else {
        echo sprintf("❌ %-15s : %s\n", $pair, $result['error']);
    }
}

echo "\n".str_repeat('-', 70)."\n";

// Перевіряємо БД
$rates = DB::table('exchange_rates')
    ->where('date', date('Y-m-d'))
    ->get();

echo "\n📊 Курсів у базі даних на сьогодні: ".$rates->count()."\n";

if ($rates->count() > 0) {
    echo "\n📋 Збережені курси:\n";
    foreach ($rates as $rate) {
        echo sprintf(
            "   %s -> %s = %.6f\n",
            $rate->base_currency,
            $rate->target_currency,
            $rate->rate
        );
    }
}

// Тестуємо конвертацію
echo "\n".str_repeat('=', 70)."\n";
echo "🧪 Тест конвертації:\n\n";

$tests = [
    ['amount' => 1000, 'from' => 'USD', 'to' => 'UAH'],
    ['amount' => 800, 'from' => 'PLN', 'to' => 'UAH'],
    ['amount' => 1000, 'from' => 'USD', 'to' => 'PLN'],
    ['amount' => 50000, 'from' => 'UAH', 'to' => 'USD'],
];

foreach ($tests as $test) {
    try {
        $result = $service->convert($test['amount'], $test['from'], $test['to']);
        echo sprintf(
            "   %s %s = %s %s\n",
            $service->format($test['amount'], $test['from']),
            $test['from'],
            $service->format($result, $test['to']),
            $test['to']
        );
    } catch (\Exception $e) {
        echo "   ❌ {$test['from']} -> {$test['to']}: ".$e->getMessage()."\n";
    }
}

echo "\n✅ Оновлення завершено!\n";
