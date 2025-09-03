<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
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
         // Forçar HTTPS em produção (Render.com)
         if (config('app.env') === 'production') {
             URL::forceScheme('https');
         }

         // Configurar trusted proxies para Render.com
         if (config('app.env') === 'production') {
             $this->app['request']->server->set('HTTPS', 'on');
         }
     }
    
}
