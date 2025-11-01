<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;

        // Створюємо системні категорії
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    public function test_user_can_get_categories(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'categories' => [
                        '*' => ['id', 'name', 'type', 'icon', 'color'],
                    ],
                ],
            ]);
    }

    public function test_user_can_create_category(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/categories', [
                'name' => 'Тестова категорія',
                'type' => 'expense',
                'icon' => 'test-icon',
                'color' => '#FF5733',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Категорію успішно створено',
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Тестова категорія',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_create_category_with_invalid_data(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/categories', [
                'name' => '',
                'type' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type']);
    }

    public function test_user_can_update_own_category(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/categories/{$category->id}", [
                'name' => 'Оновлена категорія',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Категорію успішно оновлено',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Оновлена категорія',
        ]);
    }

    public function test_user_cannot_update_system_category(): void
    {
        $systemCategory = Category::whereNull('user_id')->first();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/categories/{$systemCategory->id}", [
                'name' => 'Спроба оновити',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_category(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Категорію успішно видалено',
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_user_cannot_delete_system_category(): void
    {
        $systemCategory = Category::whereNull('user_id')->first();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/categories/{$systemCategory->id}");

        $response->assertStatus(403);
    }
}
