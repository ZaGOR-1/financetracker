<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Budget;
use App\Observers\TransactionObserver;
use App\Observers\CategoryObserver;
use App\Observers\BudgetObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Реєструємо Observers для автоматичного очищення кешу
        Transaction::observe(TransactionObserver::class);
        Category::observe(CategoryObserver::class);
        Budget::observe(BudgetObserver::class);
    }
}
