<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BudgetRepository implements BudgetRepositoryInterface
{
    public function getUserBudgets(int $userId, array $filters = []): Collection
    {
        $query = Budget::query()
            ->select('budgets.*') // Явно вибираємо поля з budgets
            ->where('budgets.user_id', $userId)
            ->with(['category:id,name,type,icon,color']); // Eager loading з вибором полів

        if (isset($filters['is_active'])) {
            $query->where('budgets.is_active', $filters['is_active']);
        }

        if (isset($filters['current']) && $filters['current']) {
            $query->current();
        }

        return $query->orderBy('budgets.start_date', 'desc')->get();
    }

    public function create(array $data): Budget
    {
        return Budget::create($data);
    }

    public function update(int $id, array $data): Budget
    {
        $budget = Budget::findOrFail($id);
        $budget->update($data);

        return $budget->fresh(['category:id,name,type,icon,color']);
    }

    public function delete(int $id): bool
    {
        $budget = Budget::findOrFail($id);

        return $budget->delete();
    }

    public function find(int $id): ?Budget
    {
        return Budget::with(['category:id,name,type,icon,color'])->find($id);
    }
}
