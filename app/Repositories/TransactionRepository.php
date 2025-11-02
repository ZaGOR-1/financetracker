<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getUserTransactions(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Transaction::query()
            ->select('transactions.*') // Явно вибираємо поля з transactions
            ->where('transactions.user_id', $userId)
            ->with(['category:id,name,type,icon,color']); // Eager loading з вибором полів

        // Фільтр за періодом
        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $query->betweenDates($filters['from_date'], $filters['to_date']);
        }

        // Фільтр за категорією
        if (isset($filters['category_id'])) {
            $query->where('transactions.category_id', $filters['category_id']);
        }

        // Фільтр за типом (income/expense) - використовуємо JOIN замість whereHas
        if (isset($filters['type'])) {
            $query->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->where('categories.type', $filters['type']);
        }

        return $query->orderBy('transactions.transaction_date', 'desc')
            ->orderBy('transactions.created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function update(int $id, array $data): Transaction
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update($data);

        return $transaction->fresh(['category:id,name,type,icon,color']);
    }

    public function delete(int $id): bool
    {
        $transaction = Transaction::findOrFail($id);

        return $transaction->delete();
    }

    public function find(int $id): ?Transaction
    {
        return Transaction::with(['category:id,name,type,icon,color'])->find($id);
    }

    public function getTotalAmount(int $userId, string $type, ?string $startDate = null, ?string $endDate = null): float
    {
        // Використовуємо JOIN замість whereHas для кращої продуктивності
        $query = Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('categories.type', $type);

        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }

        return (float) $query->sum('transactions.amount');
    }
}
