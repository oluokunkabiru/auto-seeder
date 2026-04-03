<?php

namespace Oluokunkabiru\AutoSeeder;

use Illuminate\Support\ServiceProvider;

class AutoSeederServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        // Make the config publishable:
        // php artisan vendor:publish --tag=auto-seeder-config
        $this->publishes([
            __DIR__ . '/../config/auto-seeder.php' => config_path('auto-seeder.php'),
        ], 'auto-seeder-config');
    }

    /**
     * Register package bindings.
     */
    public function register(): void
    {
        // Merge package defaults — works even before the user publishes the config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/auto-seeder.php',
            'auto-seeder'
        );
    }
}
