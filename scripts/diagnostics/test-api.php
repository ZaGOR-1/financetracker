<?php

/**
 * Тестовий скрипт для перевірки API статистики
 * Запуск: php test-api.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Тестування API Статистики ===\n\n";

// Отримуємо першого користувача
$user = \App\Models\User::first();

if (!$user) {
    echo "❌ Користувач не знайдений! Створіть користувача.\n";
    exit(1);
}

echo "✅ Користувач: {$user->name} (ID: {$user->id})\n\n";

// Перевіряємо транзакції
$transactionsCount = \App\Models\Transaction::where('user_id', $user->id)->count();
echo "📊 Всього транзакцій: {$transactionsCount}\n";

$currentMonthCount = \App\Models\Transaction::where('user_id', $user->id)
    ->whereYear('transaction_date', now()->year)
    ->whereMonth('transaction_date', now()->month)
    ->count();
echo "📅 Транзакцій у поточному місяці: {$currentMonthCount}\n\n";

// Тестуємо StatsService
echo "=== Тестування StatsService ===\n\n";

$statsService = app(\App\Services\StatsService::class);

try {
    // 1. Overview
    echo "1️⃣ Тестування Overview...\n";
    $overview = $statsService->getOverview($user->id);
    echo "   ✅ Доходи: ₴" . number_format($overview['total_income'], 2) . "\n";
    echo "   ✅ Витрати: ₴" . number_format($overview['total_expense'], 2) . "\n";
    echo "   ✅ Баланс: ₴" . number_format($overview['balance'], 2) . "\n";
    echo "   ✅ Транзакцій: " . $overview['transactions_count'] . "\n";
    echo "   ✅ Топ категорій: " . count($overview['top_expense_categories']) . "\n";
    
    if (count($overview['top_expense_categories']) > 0) {
        foreach ($overview['top_expense_categories'] as $cat) {
            echo "      - {$cat['category_name']}: ₴" . number_format($cat['total'], 2) . "\n";
        }
    }
    echo "\n";

    // 2. Cashflow
    echo "2️⃣ Тестування Cashflow...\n";
    $cashflow = $statsService->getCashflow($user->id, 6);
    echo "   ✅ Місяців: " . count($cashflow) . "\n";
    
    if (count($cashflow) > 0) {
        foreach ($cashflow as $month) {
            echo "      - {$month['month']}: Доходи ₴" . number_format($month['income'], 2) 
                . " / Витрати ₴" . number_format($month['expense'], 2) . "\n";
        }
    }
    echo "\n";

    // 3. Category Breakdown
    echo "3️⃣ Тестування Category Breakdown...\n";
    $breakdown = $statsService->getCategoryBreakdown($user->id);
    echo "   ✅ Категорій: " . count($breakdown) . "\n";
    
    if (count($breakdown) > 0) {
        foreach ($breakdown as $cat) {
            echo "      - {$cat['category_name']}: ₴" . number_format($cat['total'], 2) 
                . " ({$cat['percentage']}%)\n";
        }
    }
    echo "\n";

    echo "✅ Всі тести пройшли успішно!\n";
    echo "\n📝 Якщо дані порожні, додайте транзакції за поточний місяць.\n";

} catch (\Exception $e) {
    echo "❌ Помилка: " . $e->getMessage() . "\n";
    echo "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

