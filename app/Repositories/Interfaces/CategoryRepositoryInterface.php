<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Отримати всі категорії користувача (включаючи системні).
     *
     * @param  array  $filters  ['type' => 'income'|'expense', 'is_active' => bool]
     */
    public function getUserCategories(int $userId, array $filters = []): Collection;

    /**
     * Створити нову категорію.
     */
    public function create(array $data): Category;

    /**
     * Оновити категорію.
     */
    public function update(int $id, array $data): Category;

    /**
     * Видалити категорію.
     */
    public function delete(int $id): bool;

    /**
     * Знайти категорію за ID.
     */
    public function find(int $id): ?Category;
}
