<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    public function __construct(
        private CurrencyService $currencyService
    ) {}

    /**
     * Отримати курси валют
     */
    public function rates(): JsonResponse
    {
        try {
            $rates = $this->currencyService->getRates();

            return response()->json([
                'success' => true,
                'data' => [
                    'rates' => $rates,
                    'base' => 'UAH',
                    'timestamp' => now()->toIso8601String()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Помилка отримання курсів валют',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Конвертувати суму з однієї валюти в іншу
     */
    public function convert(): JsonResponse
    {
        $amount = request('amount', 0);
        $from = request('from', 'UAH');
        $to = request('to', 'UAH');

        try {
            $converted = $this->currencyService->convert($amount, $from, $to);

            return response()->json([
                'success' => true,
                'data' => [
                    'amount' => $amount,
                    'from' => $from,
                    'to' => $to,
                    'converted' => round($converted, 2),
                    'rate' => $this->currencyService->getRate($from, $to)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Помилка конвертації',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
