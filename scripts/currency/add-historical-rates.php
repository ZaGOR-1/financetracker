<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "📅 Додавання курсів для історичних дат\n\n";

// Курси на різні дати (приблизно однакові, бо це тестові дані)
$historicalRates = [
    '2025-10-01' => [41.30, 10.45],
    '2025-10-02' => [41.28, 10.48],
    '2025-10-03' => [41.26, 10.49],
    '2025-10-04' => [41.27, 10.51],
    '2025-10-05' => [41.24, 10.50],
    '2025-10-06' => [41.25, 10.50], // Вже є
];

foreach ($historicalRates as $date => $rates) {
    [$usdRate, $plnRate] = $rates;
    // USD <-> UAH
    DB::table('exchange_rates')->updateOrInsert(
        ['base_currency' => 'USD', 'target_currency' => 'UAH', 'date' => $date],
        ['rate' => $usdRate, 'created_at' => now(), 'updated_at' => now()]
    );
    DB::table('exchange_rates')->updateOrInsert(
        ['base_currency' => 'UAH', 'target_currency' => 'USD', 'date' => $date],
        ['rate' => 1 / $usdRate, 'created_at' => now(), 'updated_at' => now()]
    );

    // PLN <-> UAH
    DB::table('exchange_rates')->updateOrInsert(
        ['base_currency' => 'PLN', 'target_currency' => 'UAH', 'date' => $date],
        ['rate' => $plnRate, 'created_at' => now(), 'updated_at' => now()]
    );
    DB::table('exchange_rates')->updateOrInsert(
        ['base_currency' => 'UAH', 'target_currency' => 'PLN', 'date' => $date],
        ['rate' => 1 / $plnRate, 'created_at' => now(), 'updated_at' => now()]
    );

    // USD <-> PLN
    $usdToPlnRate = $usdRate / $plnRate;
    DB::table('exchange_rates')->updateOrInsert(
        ['base_currency' => 'USD', 'target_currency' => 'PLN', 'date' => $date],
        ['rate' => $usdToPlnRate, 'created_at' => now(), 'updated_at' => now()]
    );
    DB::table('exchange_rates')->updateOrInsert(
        ['base_currency' => 'PLN', 'target_currency' => 'USD', 'date' => $date],
        ['rate' => 1 / $usdToPlnRate, 'created_at' => now(), 'updated_at' => now()]
    );

    echo "✅ {$date}: USD={$usdRate}, PLN={$plnRate}\n";
}

echo "\n📊 Всього курсів у БД: ".DB::table('exchange_rates')->count()."\n";
echo "✅ Готово!\n";
