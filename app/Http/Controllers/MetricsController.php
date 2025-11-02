<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    /**
     * Prometheus-compatible metrics endpoint.
     */
    public function index(): Response
    {
        $metrics = [];

        // Application version
        $version = config('app.version', '1.0.0');
        $metrics[] = '# HELP app_version Application version';
        $metrics[] = '# TYPE app_version gauge';
        $metrics[] = "app_version{version=\"{$version}\"} 1";

        // Database connections
        try {
            DB::connection()->getPdo();
            $dbStatus = 1;
        } catch (\Exception $e) {
            $dbStatus = 0;
        }
        $metrics[] = '# HELP database_up Database connection status (1 = up, 0 = down)';
        $metrics[] = '# TYPE database_up gauge';
        $metrics[] = "database_up {$dbStatus}";

        // Cache status
        try {
            Cache::get('test');
            $cacheStatus = 1;
        } catch (\Exception $e) {
            $cacheStatus = 0;
        }
        $metrics[] = '# HELP cache_up Cache connection status (1 = up, 0 = down)';
        $metrics[] = '# TYPE cache_up gauge';
        $metrics[] = "cache_up {$cacheStatus}";

        // Users count
        $usersCount = DB::table('users')->count();
        $metrics[] = '# HELP users_total Total number of registered users';
        $metrics[] = '# TYPE users_total gauge';
        $metrics[] = "users_total {$usersCount}";

        // Transactions count
        $transactionsCount = DB::table('transactions')->count();
        $metrics[] = '# HELP transactions_total Total number of transactions';
        $metrics[] = '# TYPE transactions_total counter';
        $metrics[] = "transactions_total {$transactionsCount}";

        // Budgets count
        $budgetsCount = DB::table('budgets')->where('is_active', true)->count();
        $metrics[] = '# HELP budgets_active Active budgets count';
        $metrics[] = '# TYPE budgets_active gauge';
        $metrics[] = "budgets_active {$budgetsCount}";

        // Queue jobs pending
        $queuePending = DB::table('jobs')->count();
        $metrics[] = '# HELP queue_jobs_pending Pending queue jobs';
        $metrics[] = '# TYPE queue_jobs_pending gauge';
        $metrics[] = "queue_jobs_pending {$queuePending}";

        // Queue failed jobs
        $queueFailed = DB::table('failed_jobs')->count();
        $metrics[] = '# HELP queue_jobs_failed Failed queue jobs';
        $metrics[] = '# TYPE queue_jobs_failed counter';
        $metrics[] = "queue_jobs_failed {$queueFailed}";

        // Memory usage
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
        $metrics[] = '# HELP php_memory_usage_mb PHP memory usage in MB';
        $metrics[] = '# TYPE php_memory_usage_mb gauge';
        $metrics[] = sprintf('php_memory_usage_mb %.2f', $memoryUsage);

        return response(implode("\n", $metrics)."\n")
            ->header('Content-Type', 'text/plain; version=0.0.4');
    }
}
