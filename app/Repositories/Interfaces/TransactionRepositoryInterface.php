<?php

namespace App\Repositories\Interfaces;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    /**
     * Отримати транзакції користувача з пагінацією.
     *
     * @param int $userId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserTransactions(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Створити нову транзакцію.
     *
     * @param array $data
     * @return Transaction
     */
    public function create(array $data): Transaction;

    /**
     * Оновити транзакцію.
     *
     * @param int $id
     * @param array $data
     * @return Transaction
     */
    public function update(int $id, array $data): Transaction;

    /**
     * Видалити транзакцію.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Знайти транзакцію за ID.
     *
     * @param int $id
     * @return Transaction|null
     */
    public function find(int $id): ?Transaction;

    /**
     * Отримати суму транзакцій за період.
     *
     * @param int $userId
     * @param string $type 'income'|'expense'
     * @param string|null $startDate
     * @param string|null $endDate
     * @return float
     */
    public function getTotalAmount(int $userId, string $type, ?string $startDate = null, ?string $endDate = null): float;
}
