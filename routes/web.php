<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Gestionnaire\MembreEleveurController;
use App\Http\Controllers\Gestionnaire\ReceptionController;
use App\Http\Controllers\Gestionnaire\LivraisonUsineController;
use App\Http\Controllers\Gestionnaire\PaiementController;
use App\Http\Controllers\Gestionnaire\StockController;

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

        // Gestion des Réceptions de Lait
        Route::prefix('receptions')->name('receptions.')->group(function () {
            Route::get('/', [ReceptionController::class, 'index'])->name('index');
            Route::get('/create', [ReceptionController::class, 'create'])->name('create');
            Route::post('/', [ReceptionController::class, 'store'])->name('store');
            Route::delete('/{reception}', [ReceptionController::class, 'destroy'])->name('destroy');
        });

        // Gestion du Stock
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('/', [StockController::class, 'index'])->name('index');
            Route::get('/{date}', [StockController::class, 'show'])->name('show');
        });

        // Gestion des Livraisons vers l'Usine
        Route::prefix('livraisons')->name('livraisons.')->group(function () {
            Route::get('/', [LivraisonUsineController::class, 'index'])->name('index');
            Route::get('/create', [LivraisonUsineController::class, 'create'])->name('create');
            Route::post('/', [LivraisonUsineController::class, 'store'])->name('store');
            Route::put('/{livraison}/validate', [LivraisonUsineController::class, 'validate'])->name('validate');
            Route::delete('/{livraison}', [LivraisonUsineController::class, 'destroy'])->name('destroy');
        });

        // Gestion des Paiements de l'Usine - MODIFIÉ AVEC NOUVELLE ROUTE
        Route::prefix('paiements')->name('paiements.')->group(function () {
            Route::get('/', [PaiementController::class, 'index'])->name('index');
            Route::post('/calculer-periode', [PaiementController::class, 'calculerPeriode'])->name('calculer-periode');
            
            // NOUVELLE ROUTE: Valider toute une période de 15 jours
            Route::post('/valider-periode', [PaiementController::class, 'validerPeriode'])->name('valider-periode');
            
            // Route existante pour marquer un paiement individuel comme payé
            Route::put('/{paiement}/marquer-paye', [PaiementController::class, 'marquerPaye'])->name('marquer-paye');
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