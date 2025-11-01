<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;

$user = User::first();
$incomeCategories = Category::where('type', 'income')->get();
$expenseCategories = Category::where('type', 'expense')->get();

// Створюємо тестові транзакції з конкретним часом
$transactions = [
    // Доходи
    ['category' => 'Зарплата', 'amount' => 25000, 'description' => 'Зарплата за жовтень', 'date' => '2025-10-05 09:30:15'],
    ['category' => 'Фріланс', 'amount' => 3500, 'description' => 'Проект для клієнта', 'date' => '2025-10-03 14:20:45'],
    ['category' => 'Інвестиції', 'amount' => 1200, 'description' => 'Дивіденди', 'date' => '2025-10-01 10:15:00'],
    
    // Витрати
    ['category' => 'Їжа', 'amount' => 450.50, 'description' => 'Продукти в супермаркеті', 'date' => '2025-10-06 18:45:30'],
    ['category' => 'Транспорт', 'amount' => 120, 'description' => 'Таксі', 'date' => '2025-10-06 08:15:22'],
    ['category' => 'Комунальні послуги', 'amount' => 2500, 'description' => 'Оплата за квартиру', 'date' => '2025-10-04 12:00:00'],
    ['category' => 'Розваги', 'amount' => 800, 'description' => 'Кіно з друзями', 'date' => '2025-10-05 20:30:45'],
    ['category' => 'Здоров\'я', 'amount' => 350, 'description' => 'Аптека', 'date' => '2025-10-02 16:10:30'],
];

foreach ($transactions as $data) {
    $category = Category::where('name', $data['category'])->first();
    
    if ($category) {
        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => $data['amount'],
            'description' => $data['description'],
            'transaction_date' => $data['date'],
        ]);
        echo "✓ Створено: {$data['category']} - {$data['amount']} грн ({$data['date']})\n";
    }
}

// Додаткові випадкові транзакції за останній місяць
for ($i = 0; $i < 30; $i++) {
    $isIncome = rand(0, 2) === 0; // 33% доходи, 67% витрати
    $categories = $isIncome ? $incomeCategories : $expenseCategories;
    $category = $categories->random();
    
    $daysAgo = rand(1, 30);
    $hours = rand(6, 22);
    $minutes = rand(0, 59);
    $seconds = rand(0, 59);
    
    $date = now()->subDays($daysAgo)->setTime($hours, $minutes, $seconds);
    
    Transaction::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => rand(50, 5000) + (rand(0, 99) / 100),
        'description' => 'Випадкова транзакція ' . $i,
        'transaction_date' => $date,
    ]);
}

echo "\n✓ Створено 38 транзакцій з точним часом!\n";
echo "Відвідайте http://localhost:8000/transactions для перегляду\n";

