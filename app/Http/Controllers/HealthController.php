<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Simple health check endpoint.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'OK',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Detailed health check with dependencies.
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = collect($checks)->every(fn ($check) => $check['status'] === 'ok');
        $statusCode = $allHealthy ? 200 : 503;

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $statusCode);
    }

    private function checkApp(): array
    {
        try {
            $version = config('app.version', '1.0.0');
            $env = config('app.env');

            return [
                'status' => 'ok',
                'version' => $version,
                'environment' => $env,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();

            return [
                'status' => 'ok',
                'connection' => 'available',
                'database' => $dbName,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_'.time();
            Cache::put($testKey, 'test', 5);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            if ($value !== 'test') {
                throw new \Exception('Cache read/write test failed');
            }

            return [
                'status' => 'ok',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache test failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function checkStorage(): array
    {
        try {
            $storagePath = storage_path('logs');
            $isWritable = is_writable($storagePath);

            return [
                'status' => $isWritable ? 'ok' : 'error',
                'writable' => $isWritable,
                'path' => $storagePath,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed',
                'error' => $e->getMessage(),
            ];
        }
    }
}
