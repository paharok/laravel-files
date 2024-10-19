<?php

namespace Paharok\Laravelfiles\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Blade;
use Paharok\Laravelfiles\View\Components\FileFieldComponent;
use Illuminate\Routing\Router;

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
    public function boot(Router $router)
    {
        //
        $router->aliasMiddleware('plf.nocache', \Paharok\Laravelfiles\Http\Middleware\NoCache::class);

        $this->loadRoutesFrom(__DIR__.'/../routes/laravelfiles.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravelfiles');

        Blade::component('plf-field', FileFieldComponent::class);

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravelfiles');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/laravel-files'),
            __DIR__.'/../config/laravelfiles.php' => config_path('laravelfiles.php'),
        ], 'laravel-files-assets');

        $this->mergeConfigFrom(
            __DIR__.'/../config/laravelfiles.php', 'laravelfiles'
       );

    }
}
