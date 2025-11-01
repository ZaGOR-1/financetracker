<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;

echo "Створення тестових транзакцій з різними валютами...\n\n";

$user = User::first();

if (!$user) {
    echo "❌ Користувач не знайдений!\n";
    exit(1);
}

// Оновлюємо базову валюту користувача
$user->update(['default_currency' => 'UAH']);
echo "✓ Базову валюту користувача встановлено: UAH\n\n";

// Видаляємо старі транзакції
Transaction::query()->delete();
echo "✓ Видалено старі транзакції\n\n";

// Створюємо транзакції в різних валютах
$testTransactions = [
    // Доходи в UAH
    [
        'category' => 'Зарплата',
        'amount' => 25000,
        'currency' => 'UAH',
        'description' => 'Зарплата за жовтень',
        'date' => '2025-10-05 09:30:00',
    ],
    [
        'category' => 'Фріланс',
        'amount' => 3500,
        'currency' => 'UAH',
        'description' => 'Проект для українського клієнта',
        'date' => '2025-10-03 14:20:00',
    ],
    
    // Доходи в USD
    [
        'category' => 'Фріланс',
        'amount' => 500,
        'currency' => 'USD',
        'description' => 'Upwork project payment',
        'date' => '2025-10-04 16:45:00',
    ],
    [
        'category' => 'Інвестиції',
        'amount' => 150,
        'currency' => 'USD',
        'description' => 'Stock dividends',
        'date' => '2025-10-01 10:00:00',
    ],
    
    // Доходи в PLN
    [
        'category' => 'Фріланс',
        'amount' => 800,
        'currency' => 'PLN',
        'description' => 'Polska zlecenie',
        'date' => '2025-10-02 12:30:00',
    ],
    
    // Витрати в UAH
    [
        'category' => 'Їжа',
        'amount' => 450.50,
        'currency' => 'UAH',
        'description' => 'АТБ, продукти',
        'date' => '2025-10-06 18:45:30',
    ],
    [
        'category' => 'Транспорт',
        'amount' => 120,
        'currency' => 'UAH',
        'description' => 'Таксі',
        'date' => '2025-10-06 08:15:22',
    ],
    [
        'category' => 'Комунальні послуги',
        'amount' => 2500,
        'currency' => 'UAH',
        'description' => 'Квартплата',
        'date' => '2025-10-04 12:00:00',
    ],
    [
        'category' => 'Розваги',
        'amount' => 800,
        'currency' => 'UAH',
        'description' => 'Планета Кіно з друзями',
        'date' => '2025-10-05 20:30:45',
    ],
    
    // Витрати в USD
    [
        'category' => 'Покупки',
        'amount' => 89.99,
        'currency' => 'USD',
        'description' => 'Amazon: wireless headphones',
        'date' => '2025-10-03 11:20:00',
    ],
    [
        'category' => 'Освіта',
        'amount' => 29.99,
        'currency' => 'USD',
        'description' => 'Udemy course: Laravel Master',
        'date' => '2025-10-02 15:30:00',
    ],
    [
        'category' => 'Підписки',
        'amount' => 15.99,
        'currency' => 'USD',
        'description' => 'Netflix Premium',
        'date' => '2025-10-01 09:00:00',
    ],
    
    // Витрати в PLN
    [
        'category' => 'Покупки',
        'amount' => 150,
        'currency' => 'PLN',
        'description' => 'Zakupy w Biedronce',
        'date' => '2025-10-05 14:00:00',
    ],
    [
        'category' => 'Транспорт',
        'amount' => 45,
        'currency' => 'PLN',
        'description' => 'Bilet PKP Warszawa-Kraków',
        'date' => '2025-10-04 08:30:00',
    ],
    [
        'category' => 'Здоров\'я',
        'amount' => 85,
        'currency' => 'PLN',
        'description' => 'Apteka: leki',
        'date' => '2025-10-02 16:10:30',
    ],
];

$created = 0;
foreach ($testTransactions as $data) {
    $category = Category::where('name', $data['category'])->first();
    
    if ($category) {
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'description' => $data['description'],
            'transaction_date' => $data['date'],
        ]);
        
        $symbol = match($data['currency']) {
            'UAH' => '₴',
            'USD' => '$',
            'PLN' => 'zł',
            default => $data['currency'],
        };
        
        echo "✓ {$data['currency']}: {$symbol}{$data['amount']} - {$data['category']} ({$data['date']})\n";
        $created++;
    }
}

echo "\n✅ Створено {$created} транзакцій в 3 валютах (UAH, USD, PLN)!\n";
echo "\n📊 Тепер можна:\n";
echo "  1. Переглянути транзакції: http://localhost:8000/transactions\n";
echo "  2. Додати нову транзакцію з вибором валюти\n";
echo "  3. Оновити курси валют: php artisan currency:update-rates\n";

