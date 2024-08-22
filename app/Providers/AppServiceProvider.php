<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\EmployeeIncidentInterface;
use App\Services\EmployeeIncidentService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EmployeeIncidentInterface::class, EmployeeIncidentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
