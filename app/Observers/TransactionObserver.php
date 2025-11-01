<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\CacheService;

/**
 * Observer для автоматичного очищення кешу при зміні транзакцій
 */
class TransactionObserver
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * Після створення транзакції
     */
    public function created(Transaction $transaction): void
    {
        $this->clearCache($transaction);
    }

    /**
     * Після оновлення транзакції
     */
    public function updated(Transaction $transaction): void
    {
        $this->clearCache($transaction);
    }

    /**
     * Після видалення транзакції
     */
    public function deleted(Transaction $transaction): void
    {
        $this->clearCache($transaction);
    }

    /**
     * Очистити кеш користувача
     */
    private function clearCache(Transaction $transaction): void
    {
        // Очищаємо весь кеш транзакцій, статистики та бюджетів користувача
        $this->cacheService->forgetUserTransactions($transaction->user_id);
    }
}
