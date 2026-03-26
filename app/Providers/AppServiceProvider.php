<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Share platform settings globally with all views
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('platformName', \App\Models\PlatformSetting::get('platform_name', 'Naukaridarpan'));
        });
    }
}
