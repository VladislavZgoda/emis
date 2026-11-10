<?php

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\SubstationController;
use App\Http\Controllers\SubstationMeterStatController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', function () {
        return Inertia::render('home');
    })->name('home');

    Route::resource('substations', SubstationController::class)
        ->except(['index', 'create', 'edit']);

    Route::resource('substations.meter-stats', SubstationMeterStatController::class)
        ->only(['store']);

    Route::put('substations/{substation}/meter-stats', [SubstationMeterStatController::class, 'update'])
        ->name('substations.meter-stats.update');
});
