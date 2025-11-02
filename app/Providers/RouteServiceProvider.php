<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Загальний Rate Limiting для API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Занадто багато запитів. Спробуйте пізніше.',
                        'error' => 'rate_limit_exceeded'
                    ], 429, $headers);
                });
        });
        
        // Більш суворий Rate Limiting для аутентифікації (захист від brute force)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Занадто багато спроб входу. Почекайте хвилину.',
                        'error' => 'auth_rate_limit_exceeded'
                    ], 429, $headers);
                });
        });
        
        // Rate Limiting для експорту даних (ресурсномісткі операції)
        RateLimiter::for('export', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Занадто багато запитів на експорт. Спробуйте пізніше.',
                        'error' => 'export_rate_limit_exceeded'
                    ], 429, $headers);
                });
        });
        
        // Rate Limiting для email нотифікацій
        RateLimiter::for('notifications', function (Request $request) {
            return Limit::perHour(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
