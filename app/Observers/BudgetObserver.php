<?php

namespace App\Observers;

use App\Models\Budget;
use App\Services\CacheService;

/**
 * Observer для автоматичного очищення кешу при зміні бюджетів
 */
class BudgetObserver
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Після створення бюджету
     */
    public function created(Budget $budget): void
    {
        $this->clearCache($budget);
    }

    /**
     * Після оновлення бюджету
     */
    public function updated(Budget $budget): void
    {
        $this->clearCache($budget);
    }

    /**
     * Після видалення бюджету
     */
    public function deleted(Budget $budget): void
    {
        $this->clearCache($budget);
    }

    /**
     * Очистити кеш користувача
     */
    private function clearCache(Budget $budget): void
    {
        // Очищаємо кеш бюджетів користувача
        $this->cacheService->forgetUserBudgets($budget->user_id);
    }
}
