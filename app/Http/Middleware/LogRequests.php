<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request and log details
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Логуємо вхідний запит
        $this->logRequest($request);

        // Обробляємо запит
        $response = $next($request);

        // Логуємо відповідь та час виконання
        $this->logResponse($request, $response, $startTime);

        return $response;
    }

    /**
     * Логування вхідного запиту
     */
    protected function logRequest(Request $request): void
    {
        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
        ];

        // Додаємо параметри запиту (без паролів та чутливих даних)
        if ($request->method() !== 'GET') {
            $data['request_data'] = $this->sanitizeData($request->except([
                'password',
                'password_confirmation',
                'current_password',
                'token',
                '_token',
            ]));
        }

        Log::channel('requests')->info('Incoming Request', $data);
    }

    /**
     * Логування відповіді
     */
    protected function logResponse(Request $request, Response $response, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2); // мс

        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'user_id' => auth()->id(),
        ];

        // Логуємо як warning якщо запит повільний (> 1000ms)
        if ($duration > 1000) {
            Log::channel('performance')->warning('Slow Request', $data);
        }

        // Логуємо як error якщо статус >= 400
        if ($response->getStatusCode() >= 400) {
            Log::channel('errors')->error('Error Response', array_merge($data, [
                'response' => $this->getResponseContent($response),
            ]));
        } else {
            Log::channel('requests')->info('Request Completed', $data);
        }
    }

    /**
     * Очищення чутливих даних
     */
    protected function sanitizeData(array $data): array
    {
        array_walk_recursive($data, function (&$value, $key) {
            if (is_string($key) && preg_match('/password|secret|token|key/i', $key)) {
                $value = '***REDACTED***';
            }
        });

        return $data;
    }

    /**
     * Отримання контенту відповіді (безпечно)
     */
    protected function getResponseContent(Response $response): ?string
    {
        $content = $response->getContent();

        // Обмежуємо розмір логу
        if (strlen($content) > 1000) {
            return substr($content, 0, 1000).'...[truncated]';
        }

        return $content;
    }
}
