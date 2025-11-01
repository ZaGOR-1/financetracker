<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Діагностика ExchangeRate-API\n";
echo str_repeat("=", 70) . "\n\n";

// Тест 1: Перевірка .env
echo "📋 Тест 1: Конфігурація\n";
echo str_repeat("-", 70) . "\n";

$apiKey = env('EXCHANGERATE_API_KEY');
$provider = env('EXCHANGE_RATE_PROVIDER', 'nbu');

echo "API Key: " . ($apiKey ? substr($apiKey, 0, 10) . "..." : "❌ ВІДСУТНІЙ") . "\n";
echo "Provider: {$provider}\n";
echo "Config API Key: " . (config('currencies.exchange_api.exchangerate_api_key') ? '✅' : '❌') . "\n";
echo "Config Provider: " . config('currencies.exchange_api.provider', 'nbu') . "\n";

if (!$apiKey) {
    echo "\n❌ API ключ не знайдено в .env!\n";
    echo "Додайте: EXCHANGERATE_API_KEY=e326cd9f57775c9455ff9ddb\n";
    exit(1);
}

if ($provider !== 'exchangerate-api') {
    echo "\n⚠️  УВАГА: Provider не exchangerate-api, а '{$provider}'!\n";
    echo "Змініть в .env: EXCHANGE_RATE_PROVIDER=exchangerate-api\n\n";
}

// Тест 2: Прямий запит до API
echo "\n📡 Тест 2: Прямий запит до API\n";
echo str_repeat("-", 70) . "\n";

try {
    $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/pair/USD/UAH";
    echo "URL: {$url}\n\n";
    
    $response = \Illuminate\Support\Facades\Http::timeout(10)
        ->withOptions(['verify' => false]) // Для Windows SSL
        ->get($url);
    
    if ($response->successful()) {
        $data = $response->json();
        
        if ($data['result'] === 'success') {
            echo "✅ API працює!\n";
            echo "   Курс USD→UAH: {$data['conversion_rate']}\n";
            echo "   Оновлено: " . date('Y-m-d H:i:s', $data['time_last_update_unix']) . "\n";
        } else {
            echo "❌ API повернув помилку\n";
            echo "   Тип: " . ($data['error-type'] ?? 'unknown') . "\n";
        }
    } else {
        echo "❌ HTTP помилка: " . $response->status() . "\n";
        echo "   Відповідь: " . $response->body() . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

// Тест 3: CurrencyService
echo "\n🔧 Тест 3: CurrencyService\n";
echo str_repeat("-", 70) . "\n";

try {
    $service = app(\App\Services\CurrencyService::class);
    
    // Очистимо кеш для чистого тесту
    $today = now()->format('Y-m-d');
    \Cache::forget("exchange_rate:USD:UAH:{$today}");
    
    echo "Викликаємо convert(100, 'USD', 'UAH')...\n";
    $result = $service->convert(100, 'USD', 'UAH');
    
    echo "Результат: {$result} UAH\n";
    
    if ($result == 100.0 || $result == 100) {
        echo "❌ ПРОБЛЕМА: Курс 1:1 (fallback)!\n";
        echo "   API не використовується, працює fallback\n";
    } else {
        echo "✅ Конвертація працює (не fallback)\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

// Тест 4: Перевірка БД
echo "\n💾 Тест 4: Курси в базі даних\n";
echo str_repeat("-", 70) . "\n";

$today = now()->format('Y-m-d');
$todayRates = DB::table('exchange_rates')
    ->where('date', $today)
    ->get();

if ($todayRates->isEmpty()) {
    echo "❌ Сьогоднішніх курсів немає в БД\n";
} else {
    echo "✅ Знайдено {$todayRates->count()} курсів за {$today}:\n";
    foreach ($todayRates as $rate) {
        $isRealistic = $rate->rate > 1.1 || $rate->rate < 0.9; // Не 1:1
        $icon = $isRealistic ? '✅' : '⚠️';
        echo "   {$icon} {$rate->base_currency}→{$rate->target_currency}: {$rate->rate}\n";
    }
}

// Тест 5: Кеш
echo "\n🗄️  Тест 5: Кеш валют\n";
echo str_repeat("-", 70) . "\n";

$cacheKey = "exchange_rate:USD:UAH:{$today}";
$cached = \Cache::get($cacheKey);

if ($cached) {
    $isRealistic = $cached > 1.1 || $cached < 0.9;
    $icon = $isRealistic ? '✅' : '⚠️';
    echo "{$icon} USD→UAH закешовано: {$cached}\n";
    
    if (!$isRealistic) {
        echo "   ⚠️  Курс схожий на fallback 1:1\n";
        echo "   💡 Очистіть кеш: php artisan cache:clear\n";
    }
} else {
    echo "❌ Кеш порожній для USD→UAH\n";
}

// Підсумок
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 ПІДСУМОК\n";
echo str_repeat("=", 70) . "\n";

$issues = [];

if (!$apiKey) {
    $issues[] = "API ключ відсутній в .env";
}

if ($provider !== 'exchangerate-api') {
    $issues[] = "Provider встановлений на '{$provider}' замість 'exchangerate-api'";
}

if ($todayRates->isEmpty()) {
    $issues[] = "Сьогоднішніх курсів немає в БД";
}

if (empty($issues)) {
    echo "✅ Всі перевірки пройдено!\n";
} else {
    echo "❌ Знайдено проблеми:\n";
    foreach ($issues as $i => $issue) {
        echo "   " . ($i + 1) . ". {$issue}\n";
    }
    
    echo "\n💡 РІШЕННЯ:\n";
    echo "   1. Перевірте .env: EXCHANGE_RATE_PROVIDER=exchangerate-api\n";
    echo "   2. Очистіть кеш: php artisan config:clear && php artisan cache:clear\n";
    echo "   3. Оновіть курси: php artisan currency:update-rates\n";
}

echo "\n";
