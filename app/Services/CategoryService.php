<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private CacheService $cacheService
    ) {}

    /**
     * Отримати категорії користувача (з кешуванням).
     */
    public function getUserCategories(int $userId, array $filters = []): Collection
    {
        $cacheKey = $this->cacheService->categoriesKey($userId, $filters);

        return $this->cacheService->remember(
            'categories',
            $cacheKey,
            fn () => $this->categoryRepository->getUserCategories($userId, $filters)
        );
    }

    /**
     * Створити нову категорію.
     */
    public function createCategory(int $userId, array $data): Category
    {
        $data['user_id'] = $userId;
        $category = $this->categoryRepository->create($data);

        // Очищаємо кеш категорій користувача
        $this->cacheService->forgetUserCategories($userId);

        return $category;
    }

    /**
     * Отримати категорію за ID.
     */
    public function getCategoryById(int $categoryId): Category
    {
        $category = $this->categoryRepository->find($categoryId);

        if (! $category) {
            throw new \Exception('Category not found');
        }

        return $category;
    }

    /**
     * Оновити категорію.
     */
    public function updateCategory(int $categoryId, int $userId, array $data): Category
    {
        $category = $this->categoryRepository->find($categoryId);

        if (! $category) {
            throw new \Exception('Category not found');
        }

        // Не можна редагувати системні категорії
        if ($category->isSystem()) {
            throw new \Exception('Cannot edit system category', 403);
        }

        // Перевірка права власності
        if ($category->user_id !== $userId) {
            throw new \Exception('Unauthorized', 403);
        }

        $updated = $this->categoryRepository->update($categoryId, $data);

        // Очищаємо кеш категорій користувача
        $this->cacheService->forgetUserCategories($userId);

        return $updated;
    }

    /**
     * Видалити категорію.
     */
    public function deleteCategory(int $categoryId, int $userId): bool
    {
        $category = $this->categoryRepository->find($categoryId);

        if (! $category) {
            throw new \Exception('Category not found');
        }

        // Не можна видаляти системні категорії
        if ($category->isSystem()) {
            throw new \Exception('Cannot delete system category', 403);
        }

        // Перевірка права власності
        if ($category->user_id !== $userId) {
            throw new \Exception('Unauthorized', 403);
        }

        $deleted = $this->categoryRepository->delete($categoryId);

        // Очищаємо кеш категорій користувача
        if ($deleted) {
            $this->cacheService->forgetUserCategories($userId);
        }

        return $deleted;
    }
}
