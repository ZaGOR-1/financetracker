<?php

/**
 * Middleware для виявлення N+1 проблем у запитах
 *
 * Використання: додайте до app/Http/Kernel.php
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DetectNPlusOne
{
    /**
     * Порогове значення кількості запитів
     */
    private const QUERY_THRESHOLD = 20;

    /**
     * Список шаблонів запитів які вказують на N+1
     */
    private const N_PLUS_ONE_PATTERNS = [
        'select * from `categories` where `categories`.`id` =',
        'select * from `users` where `users`.`id` =',
        'select * from `budgets` where `budgets`.`user_id` =',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local', 'development')) {
            return $next($request);
        }

        $queryCount = 0;
        $queries = [];
        $nPlusOneDetected = [];

        // Слухаємо всі SQL запити
        DB::listen(function ($query) use (&$queryCount, &$queries, &$nPlusOneDetected) {
            $queryCount++;
            $sql = $query->sql;
            $queries[] = [
                'sql' => $sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];

            // Перевіряємо на N+1 паттерни
            foreach (self::N_PLUS_ONE_PATTERNS as $pattern) {
                if (str_contains($sql, $pattern)) {
                    $nPlusOneDetected[] = $sql;
                }
            }
        });

        $response = $next($request);

        // Аналізуємо після виконання запиту
        if ($queryCount > self::QUERY_THRESHOLD) {
            Log::warning('Можлива N+1 проблема виявлена', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'query_count' => $queryCount,
                'threshold' => self::QUERY_THRESHOLD,
            ]);
        }

        if (! empty($nPlusOneDetected)) {
            $grouped = array_count_values($nPlusOneDetected);

            Log::warning('N+1 запити виявлені', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'repeated_queries' => $grouped,
            ]);
        }

        // Додаємо заголовки для відладки (тільки у development)
        if (app()->environment('local')) {
            $response->headers->set('X-Query-Count', $queryCount);
            if (! empty($nPlusOneDetected)) {
                $response->headers->set('X-N-Plus-One-Detected', 'true');
            }
        }

        return $response;
    }
}
