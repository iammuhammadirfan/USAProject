<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

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

        RateLimiter::for('ticket-requests', function ($request) {
            return Limit::perMinute(70)->response(function () {
                return response()->json([
                    'status' => 429,
                    'message' => 'Too many requests. Please try again later.'
                ], 429);
            });
        });

        RateLimiter::for('manage-ticket-requests', function ($request) {
            return Limit::perMinute(70)->response(function () {

                $now = Carbon::now();
                $secondsRemaining = 60 - $now->second;

                return response()->json([
                    'status' => 429,
                    'seconds_remaining'=>$secondsRemaining
                ], 429);
            });
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
