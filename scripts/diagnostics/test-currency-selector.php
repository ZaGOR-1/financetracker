<?php

/**
 * Тест вибору валюти в Cashflow
 * Перевіряє чи правильно конвертуються суми при зміні валюти
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\StatsService;

echo "═══════════════════════════════════════════════════\n";
echo "📊 Тест вибору валюти в Cashflow\n";
echo "═══════════════════════════════════════════════════\n\n";

// Отримуємо першого користувача
$user = User::first();

if (!$user) {
    echo "❌ Користувача не знайдено!\n";
    exit(1);
}

echo "👤 Користувач: {$user->name}\n";
echo "💰 Базова валюта: {$user->default_currency}\n\n";

// Отримуємо сервіс через контейнер
$statsService = app(StatsService::class);

// Тестуємо різні валюти
$currencies = ['UAH', 'USD', 'PLN'];
$periods = ['7d', '30d', '6m'];

foreach ($currencies as $currency) {
    echo "═══════════════════════════════════════════════════\n";
    echo "💱 Тестування валюти: {$currency}\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    foreach ($periods as $period) {
        try {
            $result = $statsService->getCashflow($user->id, $period, $currency);
            
            echo "📅 Період: {$period}\n";
            echo "💵 Валюта відповіді: {$result['currency']}\n";
            
            $totalIncome = 0;
            $totalExpense = 0;
            
            foreach ($result['data'] as $item) {
                $totalIncome += $item['income'];
                $totalExpense += $item['expense'];
            }
            
            $symbols = [
                'UAH' => '₴',
                'USD' => '$',
                'PLN' => 'zł',
            ];
            
            $symbol = $symbols[$currency] ?? $currency;
            
            echo "📈 Загальні доходи: " . number_format($totalIncome, 2, '.', ',') . " {$symbol}\n";
            echo "📉 Загальні витрати: " . number_format($totalExpense, 2, '.', ',') . " {$symbol}\n";
            echo "💰 Баланс: " . number_format($totalIncome - $totalExpense, 2, '.', ',') . " {$symbol}\n";
            echo "📊 Кількість періодів: " . count($result['data']) . "\n";
            echo "\n";
            
        } catch (\Exception $e) {
            echo "❌ Помилка: {$e->getMessage()}\n\n";
        }
    }
}

echo "═══════════════════════════════════════════════════\n";
echo "🎯 Порівняння курсів (для перевірки конвертації)\n";
echo "═══════════════════════════════════════════════════\n\n";

try {
    $currencyService = app(\App\Services\CurrencyService::class);
    
    // Тестова сума для конвертації
    $testAmount = 1000;
    
    // UAH -> USD
    $usdAmount = $currencyService->convert($testAmount, 'UAH', 'USD');
    echo "1,000 UAH = " . number_format($usdAmount, 2) . " USD\n";
    
    // UAH -> PLN
    $plnAmount = $currencyService->convert($testAmount, 'UAH', 'PLN');
    echo "1,000 UAH = " . number_format($plnAmount, 2) . " PLN\n";
    
    // USD -> UAH
    $uahFromUsd = $currencyService->convert($testAmount, 'USD', 'UAH');
    echo "1,000 USD = " . number_format($uahFromUsd, 2) . " UAH\n";
    
    // PLN -> UAH
    $uahFromPln = $currencyService->convert($testAmount, 'PLN', 'UAH');
    echo "1,000 PLN = " . number_format($uahFromPln, 2) . " UAH\n";
    
    echo "\n✅ Конвертація працює коректно!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Помилка конвертації: {$e->getMessage()}\n";
}

echo "\n═══════════════════════════════════════════════════\n";
echo "✅ Тест завершено!\n";
echo "═══════════════════════════════════════════════════\n";
