<?php

use Illuminate\Support\Facades\Route;
use Oluokunkabiru\AutoSeeder\Http\AutoSeederController;

$prefix     = config('auto-seeder.route_prefix', 'auto-seeder');
$middleware = config('auto-seeder.route_middleware', ['web']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->name('auto-seeder.')
    ->group(function () {
        Route::get('/',          [AutoSeederController::class, 'index'])->name('dashboard');
        Route::post('/seed',     [AutoSeederController::class, 'seed'])->name('seed');
        Route::get('/settings',  [AutoSeederController::class, 'settingsShow'])->name('settings');
        Route::post('/settings', [AutoSeederController::class, 'settingsSave'])->name('settings.save');
    });
