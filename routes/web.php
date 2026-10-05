<?php

use App\Http\Controllers\SubstationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('home');
})->name('home');

Route::resource('substations', SubstationController::class)->except(['index', 'create', 'edit']);
