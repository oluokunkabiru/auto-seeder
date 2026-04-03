<?php

namespace Oluokunkabiru\AutoSeeder;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Oluokunkabiru\AutoSeeder\Commands\SeedAutoCommand;
use Oluokunkabiru\AutoSeeder\Http\Livewire\Dashboard;

class AutoSeederServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        // ── Config ───────────────────────────────────────────
        $this->publishes([
            __DIR__ . '/../config/auto-seeder.php' => config_path('auto-seeder.php'),
        ], 'auto-seeder-config');

        // ── Views ─────────────────────────────────────────────
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'auto-seeder');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/auto-seeder'),
        ], 'auto-seeder-views');

        // ── Livewire Component ────────────────────────────────
        if (class_exists(Livewire::class)) {
            Livewire::component('auto-seeder-dashboard', Dashboard::class);
        }

        // ── Routes ────────────────────────────────────────────
        if (config('auto-seeder.dashboard_enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        // ── Artisan Commands ──────────────────────────────────
        if ($this->app->runningInConsole()) {
            $this->commands([
                SeedAutoCommand::class,
            ]);
        }
    }

    /**
     * Register package bindings.
     */
    public function register(): void
    {
        // Merge package defaults so config() works even if user hasn't published
        $this->mergeConfigFrom(
            __DIR__ . '/../config/auto-seeder.php',
            'auto-seeder'
        );
    }
}
