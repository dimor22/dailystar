<?php

namespace App\Providers;

use App\Services\PlanGate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PlanGate::class);
    }

    public function boot(): void
    {
        //
    }
}
