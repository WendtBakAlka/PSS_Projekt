<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        if (request()->getHost() !== 'localhost') {
            //URL::forceScheme('https');
        }

        if (str_contains(request()->getHost(), 'trycloudflare.com')) {
            config(['session.domain' => request()->getHost()]);
        }
    }
}
