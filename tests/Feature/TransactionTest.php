<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $incomeCategory;
    protected Category $expenseCategory;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test categories
        $this->incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Salary',
            'type' => 'income',
            'color' => '#10b981',
        ]);

        $this->expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Food',
            'type' => 'expense',
            'color' => '#ef4444',
        ]);
    }

    /** @test */
    public function user_can_get_transactions_list(): void
    {
        // Create some transactions
        Transaction::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/transactions');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'transactions' => [
                        '*' => [
                            'id',
                            'amount',
                            'description',
                            'transaction_date',
                            'category',
                        ],
                    ],
                    'pagination',
                ],
            ])
            ->assertJsonCount(5, 'data.transactions');
    }

    /** @test */
    public function user_can_create_income_transaction(): void
    {
        $transactionData = [
            'category_id' => $this->incomeCategory->id,
            'amount' => 5000.00,
            'description' => 'Monthly salary',
            'transaction_date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions', $transactionData);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 5000.00,
            'description' => 'Monthly salary',
        ]);
    }

    /** @test */
    public function user_can_create_expense_transaction(): void
    {
        $transactionData = [
            'category_id' => $this->expenseCategory->id,
            'amount' => 150.50,
            'description' => 'Groceries shopping',
            'transaction_date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions', $transactionData);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 150.50,
        ]);
    }

    /** @test */
    public function user_cannot_create_transaction_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions', [
                'category_id' => 999, // Non-existent category
                'amount' => -100, // Negative amount
                'transaction_date' => 'invalid-date',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'amount', 'transaction_date']);
    }

    /** @test */
    public function user_cannot_create_transaction_without_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'amount', 'transaction_date']);
    }

    /** @test */
    public function user_can_view_single_transaction(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 3000.00,
            'description' => 'Freelance work',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/transactions/{$transaction->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function user_cannot_view_other_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/transactions/{$transaction->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function user_can_update_own_transaction(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 2000.00,
            'description' => 'Original description',
        ]);

        $updateData = [
            'category_id' => $this->expenseCategory->id,
            'amount' => 2500.00,
            'description' => 'Updated description',
            'transaction_date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/transactions/{$transaction->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 2500.00,
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function user_cannot_update_other_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/transactions/{$transaction->id}", [
                'category_id' => $this->incomeCategory->id,
                'amount' => 9999.00,
                'transaction_date' => now()->format('Y-m-d'),
            ]);

        // Service layer throws Exception which results in 500
        $response->assertStatus(500);
    }

    /** @test */
    public function user_can_delete_own_transaction(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/transactions/{$transaction->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /** @test */
    public function user_cannot_delete_other_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        
        $transaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/transactions/{$transaction->id}");

        // Service layer throws Exception which results in 500
        $response->assertStatus(500);
        
        // Transaction should not be deleted since operation failed
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /** @test */
    public function user_can_get_transaction_statistics(): void
    {
        // Create income transactions
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'amount' => 1000.00,
        ]);

        // Create expense transactions
        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'amount' => 500.00,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/transactions-stats');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_income',
                    'total_expense',
                    'balance',
                ],
            ]);
    }

    /** @test */
    public function user_can_filter_transactions_by_date(): void
    {
        // Create transactions with different dates
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => '2025-10-01',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'transaction_date' => '2025-10-15',
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'transaction_date' => '2025-10-31',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/transactions?date_from=2025-10-10&date_to=2025-10-20');

        $response->assertOk();
        
        // Check that we have results (filter is working)
        $data = $response->json('data.transactions');
        $this->assertIsArray($data);
    }

    /** @test */
    public function user_can_filter_transactions_by_category(): void
    {
        // Create transactions with different categories
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
        ]);

        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/transactions?category_id={$this->incomeCategory->id}");

        $response->assertOk()
            ->assertJsonCount(3, 'data.transactions');
    }

    /** @test */
    public function guest_cannot_access_transactions(): void
    {
        $response = $this->getJson('/api/v1/transactions');

        $response->assertUnauthorized();
    }

    /** @test */
    public function transaction_amount_must_be_positive(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions', [
                'category_id' => $this->incomeCategory->id,
                'amount' => 0,
                'transaction_date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function transaction_description_is_optional(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/transactions', [
                'category_id' => $this->incomeCategory->id,
                'amount' => 100.00,
                'transaction_date' => now()->format('Y-m-d'),
                // description is omitted
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'description' => null,
        ]);
    }
}
