<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->logExceptionWithContext($e);
        });
    }

    /**
     * Логування винятків з детальним контекстом
     */
    protected function logExceptionWithContext(Throwable $exception): void
    {
        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'input' => $this->sanitizeInput(request()->except([
                'password',
                'password_confirmation',
                'current_password',
                '_token',
            ])),
            'headers' => request()->headers->all(),
        ];

        // Логуємо в окремий канал помилок
        \Log::channel('errors')->error('Exception Occurred', $context);

        // Якщо це база даних - логуємо окремо
        if ($this->isDatabaseException($exception)) {
            \Log::channel('queries')->error('Database Exception', $context);
        }

        // Якщо це аутентифікація - логуємо в security
        if ($this->isAuthException($exception)) {
            \Log::channel('security')->warning('Authentication Exception', $context);
        }
    }

    /**
     * Перевірка чи це помилка бази даних
     */
    protected function isDatabaseException(Throwable $exception): bool
    {
        return $exception instanceof \Illuminate\Database\QueryException ||
               $exception instanceof \PDOException;
    }

    /**
     * Перевірка чи це помилка аутентифікації
     */
    protected function isAuthException(Throwable $exception): bool
    {
        return $exception instanceof \Illuminate\Auth\AuthenticationException ||
               $exception instanceof \Illuminate\Auth\Access\AuthorizationException;
    }

    /**
     * Очищення вхідних даних від чутливої інформації
     */
    protected function sanitizeInput(array $input): array
    {
        array_walk_recursive($input, function (&$value, $key) {
            if (is_string($key) && preg_match('/password|secret|token|key|card/i', $key)) {
                $value = '***REDACTED***';
            }
        });

        return $input;
    }
}
