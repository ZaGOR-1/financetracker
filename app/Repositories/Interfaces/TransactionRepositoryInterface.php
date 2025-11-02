<?php

namespace App\Repositories\Interfaces;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    /**
     * Отримати транзакції користувача з пагінацією.
     */
    public function getUserTransactions(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Створити нову транзакцію.
     */
    public function create(array $data): Transaction;

    /**
     * Оновити транзакцію.
     */
    public function update(int $id, array $data): Transaction;

    /**
     * Видалити транзакцію.
     */
    public function delete(int $id): bool;

    /**
     * Знайти транзакцію за ID.
     */
    public function find(int $id): ?Transaction;

    /**
     * Отримати суму транзакцій за період.
     *
     * @param  string  $type  'income'|'expense'
     */
    public function getTotalAmount(int $userId, string $type, ?string $startDate = null, ?string $endDate = null): float;
}
