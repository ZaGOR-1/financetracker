<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BudgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(
        private BudgetService $budgetService
    ) {}

    /**
     * Отримати список бюджетів користувача.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['is_active', 'current']);

        $budgets = $this->budgetService->getUserBudgets(
            $request->user()->id,
            $filters
        );

        return response()->json([
            'success' => true,
            'data' => [
                'budgets' => $budgets,
            ],
        ]);
    }

    /**
     * Створити новий бюджет.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'alert_threshold' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = $request->user()->id;

        $budget = $this->budgetService->createBudget($validated);

        return response()->json([
            'success' => true,
            'message' => 'Бюджет успішно створено',
            'data' => [
                'budget' => $budget,
            ],
        ], 201);
    }

    /**
     * Отримати бюджет за ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $budget = $this->budgetService->getBudgetById($id, $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => [
                'budget' => $budget,
            ],
        ]);
    }

    /**
     * Оновити бюджет.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'amount' => 'numeric|min:0.01',
            'period' => 'in:daily,weekly,monthly,yearly',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'alert_threshold' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $budget = $this->budgetService->updateBudget(
            $id,
            $request->user()->id,
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Бюджет успішно оновлено',
            'data' => [
                'budget' => $budget,
            ],
        ]);
    }

    /**
     * Видалити бюджет.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->budgetService->deleteBudget($id, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Бюджет успішно видалено',
        ]);
    }
}
