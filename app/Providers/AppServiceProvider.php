<?php

namespace App\Providers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Observers\BudgetObserver;
use App\Observers\CategoryObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\ServiceProvider;

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

        // Логування SQL запитів
        $this->setupQueryLogging();
    }

    /**
     * Налаштування логування SQL запитів
     */
    protected function setupQueryLogging(): void
    {
        if (! config('app.debug')) {
            return; // Логуємо тільки в режимі debug
        }

        \DB::listen(function ($query) {
            $sql = $query->sql;
            $bindings = $query->bindings;
            $time = $query->time;

            // Замінюємо placeholders на реальні значення для читабельності
            foreach ($bindings as $binding) {
                $value = is_numeric($binding) ? $binding : "'".$binding."'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }

            // Логуємо всі запити в development
            \Log::channel('queries')->debug('SQL Query', [
                'sql' => $sql,
                'time_ms' => $time,
                'bindings' => $bindings,
            ]);

            // Окремо логуємо повільні запити (> 100ms)
            if ($time > 100) {
                \Log::channel('slow_queries')->warning('Slow Query Detected', [
                    'sql' => $sql,
                    'time_ms' => $time,
                    'bindings' => $bindings,
                    'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                ]);
            }
        });
    }
}
