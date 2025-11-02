<?php

namespace App\Repositories\Interfaces;

use App\Models\Budget;
use Illuminate\Database\Eloquent\Collection;

interface BudgetRepositoryInterface
{
    /**
     * Отримати всі бюджети користувача.
     *
     * @param  array  $filters  ['is_active' => bool, 'current' => bool]
     */
    public function getUserBudgets(int $userId, array $filters = []): Collection;

    /**
     * Створити новий бюджет.
     */
    public function create(array $data): Budget;

    /**
     * Оновити бюджет.
     */
    public function update(int $id, array $data): Budget;

    /**
     * Видалити бюджет.
     */
    public function delete(int $id): bool;

    /**
     * Знайти бюджет за ID.
     */
    public function find(int $id): ?Budget;
}
