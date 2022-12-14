<?php

namespace Paharok\Laravelfiles\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Blade;
use Paharok\Laravelfiles\View\Components\FileFieldComponent;

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

        $test = Blade::component('plf-field', FileFieldComponent::class);

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravelfiles');

//        $this->publishes([
//            __DIR__.'/../config/laravelfiles.php' => config_path('laravelfiles.php'),
//        ]);
//        $this->mergeConfigFrom(
//            __DIR__.'/../config/laravelfiles.php', 'laravelfiles'
//        );

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/laravel-files'),
        ], 'laravelfiles');

    }
}
