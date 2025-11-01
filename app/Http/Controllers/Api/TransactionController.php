<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private CacheService $cacheService
    ) {}

    /**
     * Отримати список транзакцій користувача з фільтрацією та пагінацією.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'date_from',
            'date_to',
            'category_id',
            'type',
        ]);

        $perPage = $request->get('per_page', 15);

        $transactions = $this->transactionService->getUserTransactions(
            $request->user()->id,
            $filters,
            $perPage
        );

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                ],
            ],
        ]);
    }

    /**
     * Створити нову транзакцію.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'transaction_date' => 'required|date|before_or_equal:today',
        ]);

        $transaction = $this->transactionService->createTransaction(
            $request->user()->id,
            $validated
        );

        // Очистити кеш статистики для користувача
        $this->clearStatsCache($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Транзакцію успішно створено',
            'data' => [
                'transaction' => $transaction->load('category'),
            ],
        ], 201);
    }

    /**
     * Отримати транзакцію за ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $transaction = $this->transactionService->getTransactionById(
            $id,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'data' => [
                'transaction' => $transaction->load('category'),
            ],
        ]);
    }

    /**
     * Оновити транзакцію.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'exists:categories,id',
            'amount' => 'numeric|min:0.01',
            'description' => 'nullable|string|max:1000',
            'transaction_date' => 'date|before_or_equal:today',
        ]);

        $transaction = $this->transactionService->updateTransaction(
            $id,
            $request->user()->id,
            $validated
        );

        // Очистити кеш статистики для користувача
        $this->clearStatsCache($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Транзакцію успішно оновлено',
            'data' => [
                'transaction' => $transaction->load('category'),
            ],
        ]);
    }

    /**
     * Видалити транзакцію.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->transactionService->deleteTransaction($id, $request->user()->id);

        // Очистити кеш статистики для користувача
        $this->clearStatsCache($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Транзакцію успішно видалено',
        ]);
    }

    /**
     * Отримати статистику за транзакціями.
     */
    public function stats(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $startDate = $filters['date_from'] ?? null;
        $endDate = $filters['date_to'] ?? null;

        $stats = [
            'total_income' => $this->transactionService->getTotalAmount(
                $request->user()->id,
                'income',
                $startDate,
                $endDate
            ),
            'total_expense' => $this->transactionService->getTotalAmount(
                $request->user()->id,
                'expense',
                $startDate,
                $endDate
            ),
        ];

        $stats['balance'] = $stats['total_income'] - $stats['total_expense'];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Очистити кеш статистики для користувача.
     */
    private function clearStatsCache(int $userId): void
    {
        // Використовуємо CacheService для очищення всіх пов'язаних кешів
        $this->cacheService->forgetUserTransactions($userId);
    }
}
