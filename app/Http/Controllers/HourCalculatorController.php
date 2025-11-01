<?php

namespace App\Http\Controllers;

use App\Models\HourCalculation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HourCalculatorController extends Controller
{
    /**
     * Показати калькулятор годин
     */
    public function index(): View
    {
        $calculations = auth()->user()
            ->hourCalculations()
            ->latest()
            ->limit(10)
            ->get();

        return view('hours-calculator.index', compact('calculations'));
    }

    /**
     * Зберегти розрахунок
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hours' => 'required|numeric|min:0.01|max:24',
            'hourly_rate' => 'required|numeric|min:0.01',
            'currency' => 'required|in:UAH,USD,PLN,EUR',
            'name' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = auth()->id();

        HourCalculation::create($validated);

        return redirect()
            ->route('hours-calculator.index')
            ->with('success', 'Розрахунок збережено!');
    }

    /**
     * Видалити розрахунок
     */
    public function destroy(HourCalculation $hourCalculation): RedirectResponse
    {
        // Перевірка що розрахунок належить поточному користувачу
        if ($hourCalculation->user_id !== auth()->id()) {
            abort(403);
        }

        $hourCalculation->delete();

        return redirect()
            ->route('hours-calculator.index')
            ->with('success', 'Розрахунок видалено!');
    }
}
