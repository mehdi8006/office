<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Gestionnaire\MembreEleveurController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Login routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard Routes (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    // Éleveur Dashboard
    Route::get('/eleveur/dashboard', function () {
        return view('eleveur.dashboard');
    })->name('eleveur.dashboard');

    // USVA Dashboard
    Route::get('/usva/dashboard', function () {
        return view('usva.dashboard');
    })->name('usva.dashboard');

    // Direction Dashboard
    Route::get('/direction/dashboard', function () {
        return view('direction.dashboard');
    })->name('direction.dashboard');

    // Gestionnaire Routes
    Route::prefix('gestionnaire')->name('gestionnaire.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', function () {
            return view('gestionnaire.dashboard');
        })->name('dashboard');

        // Gestion des Membres Éleveurs
       
           Route::prefix('membres')->name('membres.')->group(function () {
            Route::get('/', [MembreEleveurController::class, 'index'])->name('index');
            Route::get('/create', [MembreEleveurController::class, 'create'])->name('create');
            Route::post('/', [MembreEleveurController::class, 'store'])->name('store');
            Route::get('/{membre}', [MembreEleveurController::class, 'show'])->name('show');
            Route::get('/{membre}/edit', [MembreEleveurController::class, 'edit'])->name('edit');
            Route::put('/{membre}', [MembreEleveurController::class, 'update'])->name('update');
            
            // Actions rapides
            Route::patch('/{membre}/activate', [MembreEleveurController::class, 'activate'])->name('activate');
            Route::patch('/{membre}/deactivate', [MembreEleveurController::class, 'deactivate'])->name('deactivate');
            Route::patch('/{membre}/restore', [MembreEleveurController::class, 'restore'])->name('restore');
            Route::delete('/{membre}', [MembreEleveurController::class, 'destroy'])->name('destroy');
        });

    });

});

/*
|--------------------------------------------------------------------------
| Default Route
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});