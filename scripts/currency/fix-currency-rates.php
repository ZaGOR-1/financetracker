<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🧪 Тест API НБУ\n\n";

$date = date('Ymd'); // Формат: 20251006
$url = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange';

echo "Запит до НБУ:\n";
echo "URL: {$url}\n";
echo "Дата: {$date}\n\n";

// Тест 1: USD
echo "1️⃣  USD:\n";
try {
    $response = Http::timeout(10)->get($url, [
        'valcode' => 'USD',
        'date' => $date,
        'json' => '',
    ]);

    echo "   Status: {$response->status()}\n";

    if ($response->successful()) {
        $data = $response->json();
        if (! empty($data)) {
            $rate = $data[0]['rate'];
            echo "   ✅ Курс: 1 USD = {$rate} UAH\n";
            echo '   Тобто: $1000 = '.number_format(1000 * $rate, 2)." грн\n\n";

            // Зберігаємо вручну
            DB::table('exchange_rates')->updateOrInsert(
                [
                    'base_currency' => 'USD',
                    'target_currency' => 'UAH',
                    'date' => date('Y-m-d'),
                ],
                [
                    'rate' => $rate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            echo "   ✅ Збережено в БД!\n\n";
        } else {
            echo "   ❌ Пусті дані від НБУ\n\n";
        }
    } else {
        echo "   ❌ Помилка: {$response->status()}\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Exception: {$e->getMessage()}\n\n";
}

// Тест 2: PLN
echo "2️⃣  PLN:\n";
try {
    $response = Http::timeout(10)->get($url, [
        'valcode' => 'PLN',
        'date' => $date,
        'json' => '',
    ]);

    if ($response->successful()) {
        $data = $response->json();
        if (! empty($data)) {
            $rate = $data[0]['rate'];
            echo "   ✅ Курс: 1 PLN = {$rate} UAH\n\n";

            DB::table('exchange_rates')->updateOrInsert(
                [
                    'base_currency' => 'PLN',
                    'target_currency' => 'UAH',
                    'date' => date('Y-m-d'),
                ],
                [
                    'rate' => $rate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            echo "   ✅ Збережено в БД!\n\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Exception: {$e->getMessage()}\n\n";
}

// Тест 3: Зворотні курси
echo "3️⃣  Обчислюємо зворотні курси (UAH -> USD, UAH -> PLN):\n";

$usdRate = DB::table('exchange_rates')
    ->where('base_currency', 'USD')
    ->where('target_currency', 'UAH')
    ->where('date', date('Y-m-d'))
    ->value('rate');

if ($usdRate) {
    $inverseRate = 1 / $usdRate;
    DB::table('exchange_rates')->updateOrInsert(
        [
            'base_currency' => 'UAH',
            'target_currency' => 'USD',
            'date' => date('Y-m-d'),
        ],
        [
            'rate' => $inverseRate,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    echo '   ✅ UAH -> USD: '.number_format($inverseRate, 6)."\n";
}

$plnRate = DB::table('exchange_rates')
    ->where('base_currency', 'PLN')
    ->where('target_currency', 'UAH')
    ->where('date', date('Y-m-d'))
    ->value('rate');

if ($plnRate) {
    $inverseRate = 1 / $plnRate;
    DB::table('exchange_rates')->updateOrInsert(
        [
            'base_currency' => 'UAH',
            'target_currency' => 'PLN',
            'date' => date('Y-m-d'),
        ],
        [
            'rate' => $inverseRate,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    echo '   ✅ UAH -> PLN: '.number_format($inverseRate, 6)."\n";
}

// Тест 4: USD -> PLN (через UAH)
if ($usdRate && $plnRate) {
    $usdToPlnRate = $usdRate / $plnRate;
    DB::table('exchange_rates')->updateOrInsert(
        [
            'base_currency' => 'USD',
            'target_currency' => 'PLN',
            'date' => date('Y-m-d'),
        ],
        [
            'rate' => $usdToPlnRate,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    echo '   ✅ USD -> PLN: '.number_format($usdToPlnRate, 6)."\n";

    $plnToUsdRate = 1 / $usdToPlnRate;
    DB::table('exchange_rates')->updateOrInsert(
        [
            'base_currency' => 'PLN',
            'target_currency' => 'USD',
            'date' => date('Y-m-d'),
        ],
        [
            'rate' => $plnToUsdRate,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    echo '   ✅ PLN -> USD: '.number_format($plnToUsdRate, 6)."\n";
}

echo "\n✅ Готово! Курси оновлено.\n";
echo "\n📊 Всього курсів у БД: ".DB::table('exchange_rates')->count()."\n";
