<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemCategories = [
            // Доходи
            [
                'user_id' => null,
                'name' => 'Зарплата',
                'type' => 'income',
                'icon' => 'wallet',
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Фріланс',
                'type' => 'income',
                'icon' => 'briefcase',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Інвестиції',
                'type' => 'income',
                'icon' => 'trending-up',
                'color' => '#8B5CF6',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Подарунки',
                'type' => 'income',
                'icon' => 'gift',
                'color' => '#EC4899',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Інше (дохід)',
                'type' => 'income',
                'icon' => 'plus-circle',
                'color' => '#6B7280',
                'is_active' => true,
            ],

            // Витрати
            [
                'user_id' => null,
                'name' => 'Їжа',
                'type' => 'expense',
                'icon' => 'shopping-cart',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Транспорт',
                'type' => 'expense',
                'icon' => 'car',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Житло',
                'type' => 'expense',
                'icon' => 'home',
                'color' => '#6366F1',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Розваги',
                'type' => 'expense',
                'icon' => 'film',
                'color' => '#EC4899',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Здоров\'я',
                'type' => 'expense',
                'icon' => 'heart',
                'color' => '#14B8A6',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Освіта',
                'type' => 'expense',
                'icon' => 'book',
                'color' => '#8B5CF6',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Одяг',
                'type' => 'expense',
                'icon' => 'shopping-bag',
                'color' => '#F97316',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Комунальні послуги',
                'type' => 'expense',
                'icon' => 'zap',
                'color' => '#EAB308',
                'is_active' => true,
            ],
            [
                'user_id' => null,
                'name' => 'Інше (витрата)',
                'type' => 'expense',
                'icon' => 'minus-circle',
                'color' => '#6B7280',
                'is_active' => true,
            ],
        ];

        foreach ($systemCategories as $category) {
            Category::firstOrCreate(
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                    'user_id' => null,
                ],
                $category
            );
        }

        $this->command->info('Системні категорії створено успішно!');
    }
}
