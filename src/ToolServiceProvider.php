<?php

namespace Elbytes\NovaTwoFactor;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Nova;
use Elbytes\NovaTwoFactor\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-two-factor');

        $this->app->booted(function () {
            $this->routes();
        });

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/nova-two-factor.php' => config_path('nova-two-factor.php'),
            ], 'nova-two-factor.config');

            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations')
            ], 'migrations');
        }


        Nova::serving(function (ServingNova $event) {
            //
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', Authenticate::class, Authorize::class], 'nova-two-factor')
            ->group(__DIR__.'/../routes/inertia.php');

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/nova-two-factor')
                ->group(__DIR__.'/../routes/api.php');

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nova-two-factor.php', 'nova-two-factor');

    }
}
