<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\StatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private StatsService $statsService
    ) {}

    /**
     * Показати дашборд.
     */
    public function index(): View
    {
        return view('dashboard.index');
    }

    /**
     * Показати спрощений дашборд (без JS/API).
     */
    public function simple(): View
    {
        $userId = auth()->id();

        // Get stats from service
        $stats = $this->statsService->getOverview($userId);

        // Get counts
        $transactionsCount = Transaction::where('user_id', $userId)->count();
        $categoriesCount = Category::count();

        return view('dashboard.simple', [
            'stats' => $stats,
            'transactions_count' => $transactionsCount,
            'categories_count' => $categoriesCount,
        ]);
    }
}
