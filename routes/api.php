<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Префікс /api/v1 для версіонування API
Route::prefix('v1')->group(function () {
    
    // Публічні маршрути (авторизація) з суворим rate limiting
    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']); // 5 спроб/хв
        Route::post('/login', [AuthController::class, 'login']); // 5 спроб/хв
    });

    // Захищені маршрути (потребують авторизації) - підтримка Web Session та Sanctum
    Route::middleware(['auth:sanctum,web', 'throttle:api'])->group(function () {
        
        // Авторизація
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        // Категорії
        Route::apiResource('categories', CategoryController::class);

        // Транзакції
        Route::apiResource('transactions', TransactionController::class);
        Route::get('transactions-stats', [TransactionController::class, 'stats']);

        // Бюджети
        Route::apiResource('budgets', BudgetController::class);

        // Статистика
        Route::prefix('stats')->group(function () {
            Route::get('/overview', [StatsController::class, 'overview']);
            Route::get('/cashflow', [StatsController::class, 'cashflow']);
            Route::get('/category-breakdown', [StatsController::class, 'categoryBreakdown']);
        });
    });
});
