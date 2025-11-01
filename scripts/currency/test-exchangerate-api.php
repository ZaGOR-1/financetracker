<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 Тестування ExchangeRate-API.com\n";
echo str_repeat("=", 60) . "\n\n";

// Отримуємо API ключ з конфігурації
$apiKey = config('currencies.exchange_api.exchangerate_api_key');

if (!$apiKey) {
    echo "❌ API ключ не знайдено в конфігурації!\n";
    echo "Перевірте .env файл: EXCHANGERATE_API_KEY\n";
    exit(1);
}

echo "✅ API ключ знайдено: " . substr($apiKey, 0, 10) . "...\n\n";

// Тестуємо з'єднання з API
try {
    echo "📡 Запит до API для курсу USD...\n";
    
    $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/latest/USD";
    $response = Http::timeout(10)
        ->withOptions(['verify' => false])
        ->get($url);
    
    if (!$response->successful()) {
        echo "❌ Помилка API: " . $response->status() . "\n";
        echo "Відповідь: " . $response->body() . "\n";
        exit(1);
    }
    
    $data = $response->json();
    
    if ($data['result'] !== 'success') {
        echo "❌ API повернув помилку: " . ($data['error-type'] ?? 'unknown') . "\n";
        exit(1);
    }
    
    echo "✅ API працює успішно!\n\n";
    
    echo "📊 Курси валют (1 USD = X):\n";
    echo str_repeat("-", 60) . "\n";
    
    $rates = $data['conversion_rates'];
    
    // Відображаємо курси для наших валют
    $currencies = ['UAH' => 'Українська гривня', 'PLN' => 'Польський злотий', 'EUR' => 'Євро'];
    
    foreach ($currencies as $code => $name) {
        if (isset($rates[$code])) {
            $rate = $rates[$code];
            echo sprintf("%-6s %-30s %10.4f\n", $code, $name, $rate);
        } else {
            echo "❌ $code не знайдено в відповіді API\n";
        }
    }
    
    echo "\n" . str_repeat("-", 60) . "\n";
    echo "⏰ Час оновлення: " . date('Y-m-d H:i:s', $data['time_last_update_unix']) . "\n";
    echo "⏰ Наступне оновлення: " . date('Y-m-d H:i:s', $data['time_next_update_unix']) . "\n";
    
    // Розраховуємо зворотні курси для UAH
    if (isset($rates['UAH']) && isset($rates['PLN'])) {
        echo "\n📈 Додаткові курси:\n";
        echo str_repeat("-", 60) . "\n";
        
        $uahToUsd = 1 / $rates['UAH'];
        $uahToPln = $rates['PLN'] / $rates['UAH'];
        $plnToUah = $rates['UAH'] / $rates['PLN'];
        
        echo sprintf("1 UAH = %.6f USD\n", $uahToUsd);
        echo sprintf("1 UAH = %.6f PLN\n", $uahToPln);
        echo sprintf("1 PLN = %.4f UAH\n", $plnToUah);
    }
    
    echo "\n✅ Всі тести пройдено успішно!\n";
    
} catch (\Exception $e) {
    echo "❌ Помилка з'єднання: " . $e->getMessage() . "\n";
    exit(1);
}

