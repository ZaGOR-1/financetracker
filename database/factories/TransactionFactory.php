<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Випадково обираємо тип транзакції
        $type = $this->faker->randomElement(['income', 'expense']);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory()->state(['type' => $type]),
            'type' => $type,
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'description' => $this->faker->optional(0.7)->sentence(),
            'transaction_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Транзакція доходу.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'category_id' => Category::factory()->state(['type' => 'income']),
        ]);
    }

    /**
     * Транзакція витрати.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'category_id' => Category::factory()->state(['type' => 'expense']),
        ]);
    }
}
