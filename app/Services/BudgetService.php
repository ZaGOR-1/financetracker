<?php

namespace App\Services;

use App\Models\Budget;
use App\Repositories\Interfaces\BudgetRepositoryInterface;

class BudgetService
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepository,
        private CacheService $cacheService
    ) {}

    /**
     * Отримати бюджети користувача з обчисленими даними (з кешуванням).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUserBudgets(int $userId, array $filters = [])
    {
        $cacheKey = $this->cacheService->budgetsKey($userId, $filters);

        return $this->cacheService->remember(
            'budgets',
            $cacheKey,
            function () use ($userId, $filters) {
                $budgets = $this->budgetRepository->getUserBudgets($userId, $filters);

                // Додати обчислені поля
                return $budgets->map(function (Budget $budget) {
                    return [
                        'id' => $budget->id,
                        'category' => $budget->category ? [
                            'id' => $budget->category->id,
                            'name' => $budget->category->name,
                        ] : null,
                        'amount' => $budget->amount,
                        'period' => $budget->period,
                        'start_date' => $budget->start_date->format('Y-m-d'),
                        'end_date' => $budget->end_date->format('Y-m-d'),
                        'spent' => $budget->spent,
                        'remaining' => $budget->remaining,
                        'percentage' => $budget->percentage,
                        'alert_threshold' => $budget->alert_threshold,
                        'is_over_budget' => $budget->isOverBudget(),
                        'is_alert_triggered' => $budget->isAlertTriggered(),
                        'is_active' => $budget->is_active,
                    ];
                });
            },
            15 // 15 хвилин
        );
    }

    /**
     * Отримати бюджет за ID.
     */
    public function getBudgetById(int $budgetId, int $userId): Budget
    {
        $budget = Budget::with('category')->findOrFail($budgetId);

        if ($budget->user_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        return $budget;
    }

    /**
     * Отримати бюджети з пагінацією.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBudgets(int $userId, array $filters = [], int $perPage = 15)
    {
        $query = Budget::query()
            ->with('category')
            ->where('user_id', $userId);

        if (! empty($filters['period'])) {
            $query->where('period', $filters['period']);
        }

        if (! empty($filters['is_active'])) {
            $query->where('is_active', true);
        }

        if (! empty($filters['exceeded'])) {
            // Бюджети де витрачено більше 100%
            $query->having('percentage', '>', 100);
        }

        if (! empty($filters['warning'])) {
            // Бюджети де досягнуто порогу попередження
            $query->whereRaw('(spent / amount * 100) >= alert_threshold')
                ->having('percentage', '<=', 100);
        }

        return $query->orderBy('start_date', 'desc')->paginate($perPage);
    }

    /**
     * Створити новий бюджет.
     */
    public function createBudget(array $data): Budget
    {

        // Валідація: start_date має бути раніше end_date
        if (strtotime($data['start_date']) >= strtotime($data['end_date'])) {
            throw new \Exception('Start date must be before end date');
        }

        // Валідація: amount > 0
        if ($data['amount'] <= 0) {
            throw new \Exception('Budget amount must be greater than zero');
        }

        // Валідація: alert_threshold між 0 і 100
        if (isset($data['alert_threshold']) && ($data['alert_threshold'] < 0 || $data['alert_threshold'] > 100)) {
            throw new \Exception('Alert threshold must be between 0 and 100');
        }

        $budget = $this->budgetRepository->create($data);

        // Очищаємо кеш бюджетів користувача
        $this->cacheService->forgetUserBudgets($data['user_id']);

        return $budget;
    }

    /**
     * Оновити бюджет.
     */
    public function updateBudget(int $budgetId, int $userId, array $data): Budget
    {
        $budget = $this->budgetRepository->find($budgetId);

        if (! $budget) {
            throw new \Exception('Budget not found');
        }

        // Перевірка права власності
        if ($budget->user_id !== $userId) {
            throw new \Exception('Unauthorized');
        }

        // Валідація дат
        $startDate = $data['start_date'] ?? $budget->start_date->format('Y-m-d');
        $endDate = $data['end_date'] ?? $budget->end_date->format('Y-m-d');

        if (strtotime($startDate) >= strtotime($endDate)) {
            throw new \Exception('Start date must be before end date');
        }

        // Валідація amount
        if (isset($data['amount']) && $data['amount'] <= 0) {
            throw new \Exception('Budget amount must be greater than zero');
        }

        // Валідація alert_threshold
        if (isset($data['alert_threshold']) && ($data['alert_threshold'] < 0 || $data['alert_threshold'] > 100)) {
            throw new \Exception('Alert threshold must be between 0 and 100');
        }

        $updated = $this->budgetRepository->update($budgetId, $data);

        // Очищаємо кеш бюджетів користувача
        $this->cacheService->forgetUserBudgets($userId);

        return $updated;
    }

    /**
     * Видалити бюджет.
     */
    public function deleteBudget(int $budgetId, int $userId): bool
    {
        $budget = $this->budgetRepository->find($budgetId);

        if (! $budget) {
            throw new \Exception('Budget not found');
        }

        // Перевірка права власності
        if ($budget->user_id !== $userId) {
            throw new \Exception('Unauthorized');
        }

        $deleted = $this->budgetRepository->delete($budgetId);

        // Очищаємо кеш бюджетів користувача
        if ($deleted) {
            $this->cacheService->forgetUserBudgets($userId);
        }

        return $deleted;
    }
}
