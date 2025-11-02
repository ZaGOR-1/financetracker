<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 *
 * Додає security headers для захисту від XSS, Clickjacking, MIME sniffing та інших атак.
 *
 * @see https://owasp.org/www-project-secure-headers/
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // X-Frame-Options: Захист від Clickjacking атак
        // SAMEORIGIN дозволяє завантаження тільки з того ж домену
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options: Запобігає MIME type sniffing
        // Браузер не буде намагатися "вгадати" тип контенту
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection: Включає вбудований XSS фільтр браузера
        // mode=block блокує сторінку при виявленні XSS
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy: Контролює, скільки інформації про referrer передається
        // strict-origin-when-cross-origin - повний URL тільки для same-origin запитів
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy: Контролює доступ до браузерних API
        // Вимикаємо небезпечні API (геолокація, мікрофон, камера)
        $response->headers->set('Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=()'
        );

        // Strict-Transport-Security (HSTS): Примушує використовувати HTTPS
        //
        // ВАЖЛИВО: Активується ТІЛЬКИ при двох умовах:
        // 1. APP_ENV=production (не local, не testing)
        // 2. HTTPS запит ($request->secure() === true)
        //
        // Локальна розробка (http://127.0.0.1:8000):
        //   ✅ HSTS НЕ активується (дозволяє HTTP)
        //   ✅ Можна працювати без SSL сертифікату
        //
        // Production (https://finance-tracker.com):
        //   ✅ HSTS активується автоматично
        //   ✅ Браузер завжди використовує HTTPS
        //   ✅ Захист від SSL Stripping атак
        if ($this->shouldEnableHSTS($request)) {
            // max-age=31536000 - 1 рік (браузер запам'ятає)
            // includeSubDomains - застосовується до api.*, cdn.*, тощо
            // preload - можна додати до HSTS Preload List браузерів
            $response->headers->set('Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content-Security-Policy (CSP): Захист від XSS та injection атак
        // Визначає, звідки можна завантажувати ресурси
        if (app()->environment('production')) {
            $csp = $this->getProductionCSP();
        } else {
            $csp = $this->getDevelopmentCSP();
        }

        $response->headers->set('Content-Security-Policy', $csp);

        // X-Powered-By: Приховуємо інформацію про сервер
        $response->headers->remove('X-Powered-By');

        // Server: Приховуємо версію сервера (налаштовується в nginx/apache)
        if ($response->headers->has('Server')) {
            $response->headers->set('Server', 'WebServer');
        }

        return $response;
    }

    /**
     * Перевіряє, чи потрібно активувати HSTS
     *
     * HSTS (HTTP Strict Transport Security) активується тільки якщо:
     * 1. APP_ENV=production (не local, не testing)
     * 2. Запит йде через HTTPS ($request->secure() === true)
     */
    private function shouldEnableHSTS(Request $request): bool
    {
        return app()->environment('production') && $request->secure();
    }

    /**
     * CSP політика для production (сувора)
     */
    protected function getProductionCSP(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.bunny.net",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://api.exchangerate-api.com https://cdn.jsdelivr.net",
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            'upgrade-insecure-requests',
        ]);
    }

    /**
     * CSP політика для development (м'якша для зручності розробки)
     */
    protected function getDevelopmentCSP(): string
    {
        // Vite dev server URLs (підтримка IPv4, IPv6, localhost та альтернативні порти)
        $viteUrls = 'http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173 http://localhost:5174 http://127.0.0.1:5174 http://[::1]:5174';
        $viteWs = 'ws://localhost:5173 ws://127.0.0.1:5173 ws://[::1]:5173 ws://localhost:5174 ws://127.0.0.1:5174 ws://[::1]:5174';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdn.tailwindcss.com {$viteUrls}",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.bunny.net {$viteUrls}",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data: https: http: blob:",
            "connect-src 'self' https://api.exchangerate-api.com https://cdn.jsdelivr.net {$viteUrls} {$viteWs}",
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
    }
}
