<?php

namespace Paharok\Laravelfiles\Providers;

use Illuminate\Support\ServiceProvider;

class LaraveFilesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadRoutesFrom(__DIR__.'/../routes/laravelfiles.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravelfiles');

        $this->publishes([
            __DIR__.'/../config/laravelfiles.php' => config_path('laravelfiles.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravelfiles.php', 'laravelfiles'
        );

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/laravel-files'),
        ], 'laravelfiles');

    }
}
