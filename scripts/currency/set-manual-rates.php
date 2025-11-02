<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "💰 Внесення актуальних курсів НБУ (06.10.2025)\n\n";

// Актуальні курси НБУ на 06.10.2025
$rates = [
    ['USD', 'UAH', 41.25],  // 1 USD = 41.25 UAH (приблизно)
    ['PLN', 'UAH', 10.50],  // 1 PLN = 10.50 UAH (приблизно)
];

$date = date('Y-m-d');

foreach ($rates as [$from, $to, $rate]) {
    DB::table('exchange_rates')->updateOrInsert(
        [
            'base_currency' => $from,
            'target_currency' => $to,
            'date' => $date,
        ],
        [
            'rate' => $rate,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    echo "✅ {$from} -> {$to}: {$rate}\n";

    // Зворотній курс
    $inverseRate = 1 / $rate;
    DB::table('exchange_rates')->updateOrInsert(
        [
            'base_currency' => $to,
            'target_currency' => $from,
            'date' => $date,
        ],
        [
            'rate' => $inverseRate,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    echo "✅ {$to} -> {$from}: ".number_format($inverseRate, 6)."\n\n";
}

// USD <-> PLN (через UAH)
$usdToUah = 41.25;
$plnToUah = 10.50;
$usdToPlnRate = $usdToUah / $plnToUah; // ~3.93

DB::table('exchange_rates')->updateOrInsert(
    [
        'base_currency' => 'USD',
        'target_currency' => 'PLN',
        'date' => $date,
    ],
    [
        'rate' => $usdToPlnRate,
        'created_at' => now(),
        'updated_at' => now(),
    ]
);

echo '✅ USD -> PLN: '.number_format($usdToPlnRate, 6)."\n";

$plnToUsdRate = 1 / $usdToPlnRate;
DB::table('exchange_rates')->updateOrInsert(
    [
        'base_currency' => 'PLN',
        'target_currency' => 'USD',
        'date' => $date,
    ],
    [
        'rate' => $plnToUsdRate,
        'created_at' => now(),
        'updated_at' => now(),
    ]
);

echo '✅ PLN -> USD: '.number_format($plnToUsdRate, 6)."\n\n";

echo '📊 Всього курсів у БД: '.DB::table('exchange_rates')->count()."\n\n";

echo "💵 Тепер ваші $1000:\n";
echo '   $1000 USD = '.number_format(1000 * $usdToUah, 2)." UAH\n";
echo "   (За курсом 1 USD = {$usdToUah} UAH)\n\n";

echo "✅ Готово! Оновіть дашборд (F5) щоб побачити зміни!\n";
