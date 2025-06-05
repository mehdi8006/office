<?php

use App\Http\Controllers\AuthController;
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

});

/*
|--------------------------------------------------------------------------
| Gestionnaire Routes (Protected)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', \App\Http\Middleware\CheckRole::class.':gestionnaire'])->prefix('gestionnaire')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('gestionnaire.dashboard');
    })->name('gestionnaire.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Gestion des Membres (Éleveurs)
    |--------------------------------------------------------------------------
    */
    Route::prefix('membres')->name('gestionnaire.membres.')->group(function () {
        Route::get('/', [App\Http\Controllers\Gestionnaire\GestionnaireMembreController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Gestionnaire\GestionnaireMembreController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Gestionnaire\GestionnaireMembreController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Gestionnaire\GestionnaireMembreController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Gestionnaire\GestionnaireMembreController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Gestionnaire\GestionnaireMembreController::class, 'update'])->name('update');
        Route::post('/{id}/status', [App\Http\Controllers\Gestionnaire\GestionnaireMembreController::class, 'changeStatus'])->name('status');
        Route::get('/export/csv', [App\Http\Controllers\Gestionnaire\GestionnaireMembreController::class, 'export'])->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | Gestion des Réceptions
    |--------------------------------------------------------------------------
    */
    Route::prefix('receptions')->name('gestionnaire.receptions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'destroy'])->name('destroy');
        Route::get('/api/daily-summary', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'dailySummary'])->name('daily-summary');
        Route::get('/api/membre-autocomplete', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'getMembreAutocomplete'])->name('membre-autocomplete');
        Route::get('/export/csv', [App\Http\Controllers\Gestionnaire\GestionnaireReceptionController::class, 'export'])->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | Gestion du Stock
    |--------------------------------------------------------------------------
    */
    Route::prefix('stock')->name('gestionnaire.stock.')->group(function () {
        Route::get('/', [App\Http\Controllers\Gestionnaire\GestionnaireStockController::class, 'index'])->name('index');
        Route::get('/show', [App\Http\Controllers\Gestionnaire\GestionnaireStockController::class, 'show'])->name('show');
        Route::get('/create-livraison', [App\Http\Controllers\Gestionnaire\GestionnaireStockController::class, 'createLivraison'])->name('create-livraison');
        Route::post('/livraisons', [App\Http\Controllers\Gestionnaire\GestionnaireStockController::class, 'storeLivraison'])->name('store-livraison');
        Route::get('/livraisons', [App\Http\Controllers\Gestionnaire\GestionnaireStockController::class, 'livraisons'])->name('livraisons');
        Route::post('/livraisons/{id}/status', [App\Http\Controllers\Gestionnaire\GestionnaireStockController::class, 'updateLivraisonStatus'])->name('livraisons.status');
        Route::delete('/livraisons/{id}', [App\Http\Controllers\Gestionnaire\GestionnaireStockController::class, 'cancelLivraison'])->name('livraisons.cancel');
        Route::get('/api/chart-data', [App\Http\Controllers\Gestionnaire\GestionnaireStockController::class, 'getStockData'])->name('chart-data');
        Route::get('/export/csv', [App\Http\Controllers\Gestionnaire\GestionnaireStockController::class, 'export'])->name('export');
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