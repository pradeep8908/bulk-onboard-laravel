<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('bulk-onboard', function (Request $request) {
            return [
                Limit::perSecond(10)->by($request->ip()),
                Limit::perMinute(600)->by($request->ip()),
            ];
        });
    }
}
