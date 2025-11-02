<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function __construct(
        private StatsService $statsService
    ) {}

    /**
     * Отримати загальну статистику (дашборд).
     */
    public function overview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'currency' => 'nullable|string|in:UAH,USD,PLN,EUR',
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $validated['date_to'] ?? now()->endOfMonth()->format('Y-m-d');
        $currency = $validated['currency'] ?? null;

        $overview = $this->statsService->getOverview(
            $request->user()->id,
            $dateFrom,
            $dateTo,
            $currency
        );

        return response()->json([
            'success' => true,
            'data' => $overview,
        ]);
    }

    /**
     * Отримати дані cash flow для графіка.
     */
    public function cashflow(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'nullable|string|in:7d,14d,30d,3m,6m',
            'currency' => 'nullable|string|in:UAH,USD,PLN,EUR',
        ]);

        $period = $validated['period'] ?? '6m';
        $currency = $validated['currency'] ?? null;

        $result = $this->statsService->getCashflow(
            $request->user()->id,
            $period,
            $currency
        );

        return response()->json([
            'success' => true,
            'data' => [
                'cashflow' => $result['data'],
                'currency' => $result['currency'],
                'period' => $period,
            ],
        ]);
    }

    /**
     * Отримати розподіл витрат за категоріями.
     */
    public function categoryBreakdown(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->format('Y-m-d');
        $dateTo = $validated['date_to'] ?? now()->endOfMonth()->format('Y-m-d');

        $breakdown = $this->statsService->getCategoryBreakdown(
            $request->user()->id,
            $dateFrom,
            $dateTo
        );

        return response()->json([
            'success' => true,
            'data' => [
                'breakdown' => $breakdown,
            ],
        ]);
    }
}
