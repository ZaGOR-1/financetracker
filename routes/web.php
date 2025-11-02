<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Health check endpoints
Route::get('/health', [\App\Http\Controllers\HealthController::class, 'index']);
Route::get('/health/detailed', [\App\Http\Controllers\HealthController::class, 'detailed']);

// Metrics endpoint for Prometheus
Route::get('/metrics', [\App\Http\Controllers\MetricsController::class, 'index']);

// Security Headers Test (тільки в development)
if (app()->environment('local')) {
    Route::get('/test-security', function () {
        return view('test-security');
    })->name('test.security');
}

// Redirect root to dashboard if authenticated
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Auth Routes з rate limiting для захисту від brute-force
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle.login'); // 5 спроб/хвилину
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle.login'); // 5 спроб/хвилину
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Test page (no API)
    Route::get('/test', fn () => view('test'))->name('test');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/simple', [DashboardController::class, 'simple'])->name('dashboard.simple');

    // Categories
    Route::get('/categories', fn () => view('categories.index'))->name('categories.index');

    // Transactions
    Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
    Route::delete('/transactions-bulk-destroy', [\App\Http\Controllers\TransactionController::class, 'bulkDestroy'])->name('transactions.bulk-destroy');

    // Budgets
    Route::resource('budgets', \App\Http\Controllers\BudgetController::class);

    // Hour Calculator
    Route::get('/hours-calculator', [\App\Http\Controllers\HourCalculatorController::class, 'index'])->name('hours-calculator.index');
    Route::post('/hours-calculator', [\App\Http\Controllers\HourCalculatorController::class, 'store'])->name('hours-calculator.store');
    Route::delete('/hours-calculator/{hourCalculation}', [\App\Http\Controllers\HourCalculatorController::class, 'destroy'])->name('hours-calculator.destroy');

    // Exports
    Route::prefix('export')->name('export.')->controller(\App\Http\Controllers\ExportController::class)->group(function () {
        Route::get('/transactions', 'transactions')->name('transactions');
        Route::get('/budgets', 'budgets')->name('budgets');
    });
});
