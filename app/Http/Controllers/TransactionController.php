<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::with('category')
            ->where('user_id', auth()->id())
            ->latest('transaction_date');

        // Filters
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }
        if ($request->filled('type')) {
            $query->whereHas('category', fn ($q) => $q->where('type', $request->type));
        }

        $transactions = $query->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        $categories = Category::select('id', 'name', 'type', 'icon', 'color')
            ->where(function ($q) {
                $q->whereNull('user_id')->orWhere('user_id', auth()->id());
            })
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('transactions.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:UAH,USD,PLN',
            'description' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date|before_or_equal:now',
        ]);

        // Отримуємо тип категорії для встановлення типу транзакції
        $category = Category::findOrFail($validated['category_id']);

        Transaction::create([
            ...$validated,
            'user_id' => auth()->id(),
            'type' => $category->type, // Автоматично встановлюємо тип
        ]);

        // Очищаємо кеш статистики
        $this->clearStatsCache(auth()->id());

        return redirect()->route('transactions.index')->with('success', 'Транзакцію створено');
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        // Завантажуємо категорію для транзакції якщо ще не завантажена
        $transaction->loadMissing('category:id,name,type,icon,color');

        $categories = Category::select('id', 'name', 'type', 'icon', 'color')
            ->where(function ($q) {
                $q->whereNull('user_id')->orWhere('user_id', auth()->id());
            })
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('transactions.edit', compact('transaction', 'categories'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:UAH,USD,PLN',
            'description' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date|before_or_equal:now',
        ]);

        // Якщо змінилася категорія, оновлюємо тип
        if ($validated['category_id'] !== $transaction->category_id) {
            $category = Category::findOrFail($validated['category_id']);
            $validated['type'] = $category->type;
        }

        $transaction->update($validated);

        // Очищаємо кеш статистики
        $this->clearStatsCache(auth()->id());

        return redirect()->route('transactions.index')->with('success', 'Транзакцію оновлено');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        // Очищаємо кеш статистики
        $this->clearStatsCache(auth()->id());

        return redirect()->route('transactions.index')->with('success', 'Транзакцію видалено');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'required|integer|exists:transactions,id',
        ]);

        // Get transactions that belong to the authenticated user (без зайвих полів)
        $transactions = Transaction::select('id', 'user_id')
            ->where('user_id', auth()->id())
            ->whereIn('id', $validated['transaction_ids'])
            ->get();

        $count = $transactions->count();

        // Delete all selected transactions
        foreach ($transactions as $transaction) {
            $this->authorize('delete', $transaction);
            $transaction->delete();
        }

        // Очищаємо кеш статистики
        $this->clearStatsCache(auth()->id());

        return redirect()->route('transactions.index')
            ->with('success', "Видалено транзакцій: {$count}");
    }

    /**
     * Очистити кеш статистики користувача.
     */
    private function clearStatsCache(int $userId): void
    {
        \Illuminate\Support\Facades\Cache::forget("stats_overview_{$userId}_*");
        \Illuminate\Support\Facades\Cache::forget("stats_cashflow_{$userId}_*");
        \Illuminate\Support\Facades\Cache::forget("stats_category_breakdown_{$userId}_*");

        // Очищаємо типові ключі для поточного місяця
        $now = \Carbon\Carbon::now();
        $defaultHash = md5('nullnull');
        \Illuminate\Support\Facades\Cache::forget("stats_overview_{$userId}_{$defaultHash}");
        \Illuminate\Support\Facades\Cache::forget("stats_category_breakdown_{$userId}_{$defaultHash}");

        // Очищаємо для основних періодів cashflow
        $periods = ['7d', '14d', '30d', '3m', '6m'];
        $currencies = ['UAH', 'USD', 'PLN'];

        foreach ($periods as $period) {
            foreach ($currencies as $currency) {
                \Illuminate\Support\Facades\Cache::forget("stats_cashflow_{$userId}_{$period}_{$currency}");
            }
        }
    }
}
