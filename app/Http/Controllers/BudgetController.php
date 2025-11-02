<?php

namespace App\Http\Controllers;

use App\Services\BudgetService;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(
        private BudgetService $budgetService
    ) {}

    /**
     * Відображення списку бюджетів
     */
    public function index(Request $request)
    {
        $filters = $request->only(['period', 'status', 'is_active']);

        // Додаємо фільтр по статусу (exceeded/warning)
        if (isset($filters['status'])) {
            if ($filters['status'] === 'exceeded') {
                $filters['exceeded'] = true;
            } elseif ($filters['status'] === 'warning') {
                $filters['warning'] = true;
            }
            unset($filters['status']);
        }

        $budgets = $this->budgetService->getBudgets(
            auth()->id(),
            $filters,
            perPage: 10
        );

        return view('budgets.index', compact('budgets'));
    }

    /**
     * Форма створення бюджету
     */
    public function create()
    {
        return view('budgets.create');
    }

    /**
     * Збереження нового бюджету
     */
    public function store(Request $request)
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

        $validated['user_id'] = auth()->id();
        $validated['alert_threshold'] = $validated['alert_threshold'] ?? 80;
        $validated['is_active'] = $request->has('is_active');

        $this->budgetService->createBudget($validated);

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Бюджет успішно створено');
    }

    /**
     * Відображення деталей бюджету
     */
    public function show(int $id)
    {
        $budget = $this->budgetService->getBudgetById($id, auth()->id());

        return view('budgets.show', compact('budget'));
    }

    /**
     * Форма редагування бюджету
     */
    public function edit(int $id)
    {
        $budget = $this->budgetService->getBudgetById($id, auth()->id());

        return view('budgets.edit', compact('budget'));
    }

    /**
     * Оновлення бюджету
     */
    public function update(Request $request, int $id)
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

        $validated['alert_threshold'] = $validated['alert_threshold'] ?? 80;
        $validated['is_active'] = $request->has('is_active');

        $this->budgetService->updateBudget($id, auth()->id(), $validated);

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Бюджет успішно оновлено');
    }

    /**
     * Видалення бюджету
     */
    public function destroy(int $id)
    {
        $this->budgetService->deleteBudget($id, auth()->id());

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Бюджет успішно видалено');
    }
}
