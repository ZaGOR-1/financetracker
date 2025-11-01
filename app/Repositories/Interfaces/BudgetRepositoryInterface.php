<?php

namespace App\Repositories\Interfaces;

use App\Models\Budget;
use Illuminate\Database\Eloquent\Collection;

interface BudgetRepositoryInterface
{
    /**
     * Отримати всі бюджети користувача.
     *
     * @param int $userId
     * @param array $filters ['is_active' => bool, 'current' => bool]
     * @return Collection
     */
    public function getUserBudgets(int $userId, array $filters = []): Collection;

    /**
     * Створити новий бюджет.
     *
     * @param array $data
     * @return Budget
     */
    public function create(array $data): Budget;

    /**
     * Оновити бюджет.
     *
     * @param int $id
     * @param array $data
     * @return Budget
     */
    public function update(int $id, array $data): Budget;

    /**
     * Видалити бюджет.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Знайти бюджет за ID.
     *
     * @param int $id
     * @return Budget|null
     */
    public function find(int $id): ?Budget;
}
