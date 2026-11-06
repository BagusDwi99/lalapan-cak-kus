<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
    public function boot(): void
    {
        // Tombol pagination memakai gaya Bootstrap 5
        // (tampilannya di resources/views/vendor/pagination/bootstrap-5.blade.php).
        Paginator::useBootstrapFive();
    }
}
