<?php

require __DIR__.'/../../vendor/autoload.php';

$app = require_once __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

echo "📊 Створення тестових транзакцій для різних періодів\n";
echo str_repeat('=', 70)."\n\n";

$user = User::first();
if (! $user) {
    echo "❌ Користувач не знайдений!\n";
    exit(1);
}

echo "👤 Користувач: {$user->name}\n\n";

// Знайти або створити категорії
$incomeCategory = Category::firstOrCreate([
    'user_id' => $user->id,
    'name' => 'Зарплата',
    'type' => 'income',
], [
    'color' => '#10b981',
]);

$expenseCategory = Category::firstOrCreate([
    'user_id' => $user->id,
    'name' => 'Продукти',
    'type' => 'expense',
], [
    'color' => '#ef4444',
]);

echo "🗑️  Видаляю старі тестові транзакції...\n";
Transaction::where('user_id', $user->id)->delete();

echo "✅ Видалено\n\n";

// Створюємо транзакції для різних періодів
$transactions = [
    // Сьогодні
    ['days_ago' => 0, 'amount' => 5000, 'currency' => 'UAH', 'type' => 'income', 'desc' => 'Зарплата сьогодні'],
    ['days_ago' => 0, 'amount' => 300, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Продукти'],

    // 3 дні тому
    ['days_ago' => 3, 'amount' => 200, 'currency' => 'USD', 'type' => 'income', 'desc' => 'Фріланс'],
    ['days_ago' => 3, 'amount' => 150, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Кафе'],

    // Тиждень тому
    ['days_ago' => 7, 'amount' => 1000, 'currency' => 'PLN', 'type' => 'income', 'desc' => 'Зарплата (PLN)'],
    ['days_ago' => 7, 'amount' => 500, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Комунальні'],

    // 10 днів тому
    ['days_ago' => 10, 'amount' => 100, 'currency' => 'USD', 'type' => 'income', 'desc' => 'Бонус'],
    ['days_ago' => 10, 'amount' => 200, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Транспорт'],

    // 2 тижні тому
    ['days_ago' => 14, 'amount' => 3000, 'currency' => 'UAH', 'type' => 'income', 'desc' => 'Зарплата'],
    ['days_ago' => 14, 'amount' => 400, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Одяг'],

    // 20 днів тому
    ['days_ago' => 20, 'amount' => 150, 'currency' => 'USD', 'type' => 'income', 'desc' => 'Фріланс'],
    ['days_ago' => 20, 'amount' => 300, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Їжа'],

    // Місяць тому
    ['days_ago' => 30, 'amount' => 5000, 'currency' => 'UAH', 'type' => 'income', 'desc' => 'Зарплата минулого місяця'],
    ['days_ago' => 30, 'amount' => 600, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Комунальні'],

    // 2 місяці тому
    ['days_ago' => 60, 'amount' => 4500, 'currency' => 'UAH', 'type' => 'income', 'desc' => 'Зарплата'],
    ['days_ago' => 60, 'amount' => 800, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Оренда'],

    // 3 місяці тому
    ['days_ago' => 90, 'amount' => 4000, 'currency' => 'UAH', 'type' => 'income', 'desc' => 'Зарплата'],
    ['days_ago' => 90, 'amount' => 700, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Покупки'],

    // 4 місяці тому
    ['days_ago' => 120, 'amount' => 3800, 'currency' => 'UAH', 'type' => 'income', 'desc' => 'Зарплата'],
    ['days_ago' => 120, 'amount' => 500, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Їжа'],

    // 5 місяців тому
    ['days_ago' => 150, 'amount' => 3500, 'currency' => 'UAH', 'type' => 'income', 'desc' => 'Зарплата'],
    ['days_ago' => 150, 'amount' => 450, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Транспорт'],

    // 6 місяців тому
    ['days_ago' => 180, 'amount' => 3200, 'currency' => 'UAH', 'type' => 'income', 'desc' => 'Зарплата'],
    ['days_ago' => 180, 'amount' => 400, 'currency' => 'UAH', 'type' => 'expense', 'desc' => 'Комунальні'],
];

echo "📝 Створюю транзакції:\n";
echo str_repeat('-', 70)."\n";

foreach ($transactions as $t) {
    $date = now()->subDays($t['days_ago']);
    $category = $t['type'] === 'income' ? $incomeCategory : $expenseCategory;

    Transaction::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => $t['amount'],
        'currency' => $t['currency'],
        'description' => $t['desc'],
        'transaction_date' => $date,
    ]);

    $typeIcon = $t['type'] === 'income' ? '📈' : '📉';
    echo "{$typeIcon} {$date->format('Y-m-d')} | {$t['amount']} {$t['currency']} | {$t['desc']}\n";
}

echo "\n".str_repeat('=', 70)."\n";
echo '✅ Створено '.count($transactions)." транзакцій\n";
echo "\n💡 Тепер можна протестувати фільтри періодів на dashboard:\n";
echo '   - 7 днів: '.count(array_filter($transactions, fn ($t) => $t['days_ago'] <= 6))." транзакцій\n";
echo '   - 14 днів: '.count(array_filter($transactions, fn ($t) => $t['days_ago'] <= 13))." транзакцій\n";
echo '   - 30 днів: '.count(array_filter($transactions, fn ($t) => $t['days_ago'] <= 29))." транзакцій\n";
echo '   - 3 місяці: '.count(array_filter($transactions, fn ($t) => $t['days_ago'] <= 90))." транзакцій\n";
echo '   - 6 місяців: '.count($transactions)." транзакцій\n";
echo "\n🌐 Відкрийте: http://localhost:8000/dashboard\n";
