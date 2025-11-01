<?php

namespace App\Repositories\Interfaces;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Отримати всі категорії користувача (включаючи системні).
     *
     * @param int $userId
     * @param array $filters ['type' => 'income'|'expense', 'is_active' => bool]
     * @return Collection
     */
    public function getUserCategories(int $userId, array $filters = []): Collection;

    /**
     * Створити нову категорію.
     *
     * @param array $data
     * @return Category
     */
    public function create(array $data): Category;

    /**
     * Оновити категорію.
     *
     * @param int $id
     * @param array $data
     * @return Category
     */
    public function update(int $id, array $data): Category;

    /**
     * Видалити категорію.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Знайти категорію за ID.
     *
     * @param int $id
     * @return Category|null
     */
    public function find(int $id): ?Category;
}
