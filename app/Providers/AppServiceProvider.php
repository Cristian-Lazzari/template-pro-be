<?php

namespace App\Providers;

use App\Services\Marketing\Automation\AutomationTriggerEvaluator;
use App\Support\Currency;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AutomationTriggerEvaluator::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        view()->composer('*', function ($view) {
            $view->with('appCurrency', Currency::definition());
        });
    }
}
