<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $period = $this->faker->randomElement(['weekly', 'monthly']);
        
        $endDate = match($period) {
            'daily' => (clone $startDate)->modify('+1 day'),
            'weekly' => (clone $startDate)->modify('+1 week'),
            'monthly' => (clone $startDate)->modify('+1 month'),
            'yearly' => (clone $startDate)->modify('+1 year'),
        };

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory()->state(['type' => 'expense']),
            'amount' => $this->faker->randomFloat(2, 500, 10000),
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'alert_threshold' => $this->faker->randomElement([70, 80, 90]),
            'is_active' => true,
        ];
    }

    /**
     * Загальний бюджет (без категорії).
     */
    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => null,
        ]);
    }
}
