<?php

use Illuminate\Support\Facades\Route;
use Oluokunkabiru\AutoSeeder\Http\Livewire\Dashboard;

$prefix     = config('auto-seeder.route_prefix', 'auto-seeder');
$middleware = config('auto-seeder.route_middleware', ['web']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->name('auto-seeder.')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');
    });
