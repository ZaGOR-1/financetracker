<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Створюємо системні категорії
        $this->call(CategorySeeder::class);

        // 2. Створюємо тестового користувача
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 3. Отримуємо системні категорії
        $incomeCategories = Category::where('type', 'income')
            ->whereNull('user_id')
            ->get();
        
        $expenseCategories = Category::where('type', 'expense')
            ->whereNull('user_id')
            ->get();

        // 4. Створюємо транзакції для тестового користувача
        // Доходи (20 транзакцій)
        foreach ($incomeCategories as $category) {
            Transaction::factory()
                ->count(rand(3, 7))
                ->create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                ]);
        }

        // Витрати (50 транзакцій)
        foreach ($expenseCategories as $category) {
            Transaction::factory()
                ->count(rand(4, 8))
                ->create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                ]);
        }

        // 5. Створюємо бюджети для кількох категорій витрат (поточний місяць)
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();

        // Бюджет на їжу (80% витрачено - warning)
        Budget::create([
            'user_id' => $user->id,
            'category_id' => $expenseCategories->where('name', 'Їжа')->first()?->id,
            'amount' => 5000,
            'period' => 'monthly',
            'start_date' => $currentMonthStart,
            'end_date' => $currentMonthEnd,
            'alert_threshold' => 75,
            'is_active' => true,
        ]);

        // Бюджет на транспорт (120% витрачено - exceeded)
        Budget::create([
            'user_id' => $user->id,
            'category_id' => $expenseCategories->where('name', 'Транспорт')->first()?->id,
            'amount' => 2000,
            'period' => 'monthly',
            'start_date' => $currentMonthStart,
            'end_date' => $currentMonthEnd,
            'alert_threshold' => 80,
            'is_active' => true,
        ]);

        // Бюджет на розваги (50% витрачено - normal)
        Budget::create([
            'user_id' => $user->id,
            'category_id' => $expenseCategories->where('name', 'Розваги')->first()?->id,
            'amount' => 3000,
            'period' => 'monthly',
            'start_date' => $currentMonthStart,
            'end_date' => $currentMonthEnd,
            'alert_threshold' => 80,
            'is_active' => true,
        ]);

        // 6. Створюємо загальний місячний бюджет
        Budget::create([
            'user_id' => $user->id,
            'category_id' => null,
            'amount' => 50000,
            'period' => 'monthly',
            'start_date' => $currentMonthStart,
            'end_date' => $currentMonthEnd,
            'alert_threshold' => 80,
            'is_active' => true,
        ]);

        $this->command->info('База даних успішно заповнена тестовими даними!');
        $this->command->info("Тестовий користувач: test@example.com / password");
    }
}
