<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CacheService;

/**
 * Observer для автоматичного очищення кешу при зміні категорій
 */
class CategoryObserver
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Після створення категорії
     */
    public function created(Category $category): void
    {
        $this->clearCache($category);
    }

    /**
     * Після оновлення категорії
     */
    public function updated(Category $category): void
    {
        $this->clearCache($category);
    }

    /**
     * Після видалення категорії
     */
    public function deleted(Category $category): void
    {
        $this->clearCache($category);
    }

    /**
     * Очистити кеш користувача
     */
    private function clearCache(Category $category): void
    {
        if ($category->user_id) {
            // Очищаємо кеш категорій та статистики користувача
            $this->cacheService->forgetUserCategories($category->user_id);
        }
    }
}
