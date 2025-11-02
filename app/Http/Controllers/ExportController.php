<?php

namespace App\Http\Controllers;

use App\Exports\BudgetsExport;
use App\Exports\TransactionsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    /**
     * Експорт транзакцій у Excel.
     */
    public function transactions(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'type' => 'nullable|in:income,expense',
        ]);

        $fileName = 'transactions_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(
            new TransactionsExport(
                auth()->id(),
                $validated['date_from'] ?? null,
                $validated['date_to'] ?? null,
                $validated['type'] ?? null
            ),
            $fileName
        );
    }

    /**
     * Експорт бюджетів у Excel.
     */
    public function budgets(): BinaryFileResponse
    {
        $fileName = 'budgets_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(
            new BudgetsExport(auth()->id()),
            $fileName
        );
    }
}
