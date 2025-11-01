<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $incomeCategories = [
            ['name' => 'Зарплата', 'icon' => 'wallet', 'color' => '#10B981'],
            ['name' => 'Фріланс', 'icon' => 'briefcase', 'color' => '#3B82F6'],
            ['name' => 'Інвестиції', 'icon' => 'trending-up', 'color' => '#8B5CF6'],
            ['name' => 'Подарунки', 'icon' => 'gift', 'color' => '#EC4899'],
        ];

        $expenseCategories = [
            ['name' => 'Їжа', 'icon' => 'shopping-cart', 'color' => '#EF4444'],
            ['name' => 'Транспорт', 'icon' => 'car', 'color' => '#F59E0B'],
            ['name' => 'Житло', 'icon' => 'home', 'color' => '#6366F1'],
            ['name' => 'Розваги', 'icon' => 'film', 'color' => '#EC4899'],
            ['name' => 'Здоров\'я', 'icon' => 'heart', 'color' => '#14B8A6'],
            ['name' => 'Освіта', 'icon' => 'book', 'color' => '#8B5CF6'],
        ];

        $type = $this->faker->randomElement(['income', 'expense']);
        $categories = $type === 'income' ? $incomeCategories : $expenseCategories;
        $category = $this->faker->randomElement($categories);

        return [
            'user_id' => User::factory(),
            'name' => $category['name'],
            'type' => $type,
            'icon' => $category['icon'],
            'color' => $category['color'],
            'is_active' => true,
        ];
    }

    /**
     * Системна категорія (доступна всім користувачам).
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
