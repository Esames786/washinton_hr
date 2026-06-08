<?php

namespace App\Providers;

use App\Models\Employee;
use App\Observers\EmployeeObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Fix "Specified key was too long" on older MySQL/MariaDB
        Schema::defaultStringLength(191);

        // Register observers
        Employee::observe(EmployeeObserver::class);
    }
}
