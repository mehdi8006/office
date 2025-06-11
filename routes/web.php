<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Gestionnaire\MembreEleveurController;
use App\Http\Controllers\Gestionnaire\ReceptionController;
use App\Http\Controllers\Gestionnaire\LivraisonUsineController;
use App\Http\Controllers\Gestionnaire\PaiementController;
use App\Http\Controllers\Gestionnaire\PaiementEleveurController;
use App\Http\Controllers\Direction\CooperativeController as DirectionCooperativeController;
use App\Http\Controllers\Direction\UtilisateurController as DirectionUtilisateurController;
use App\Http\Controllers\Direction\DashboardController as DirectionDashboardController;

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
            
            // Nouvelles routes pour téléchargements
            Route::get('/{membre}/download-receptions', [MembreEleveurController::class, 'downloadReceptions'])->name('download-receptions');
            Route::get('/{membre}/download-paiements', [MembreEleveurController::class, 'downloadPaiements'])->name('download-paiements');
        });

       // Gestion des Réceptions de Lait
        Route::prefix('receptions')->name('receptions.')->group(function () {
            Route::get('/', [ReceptionController::class, 'index'])->name('index');
            Route::get('/create', [ReceptionController::class, 'create'])->name('create');
            Route::post('/', [ReceptionController::class, 'store'])->name('store');
            Route::patch('/{reception}', [ReceptionController::class, 'update'])->name('update'); // Nouvelle route
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
            Route::put('/{livraison}', [LivraisonUsineController::class, 'update'])->name('update'); // NOUVELLE ROUTE
            Route::put('/{livraison}/validate', [LivraisonUsineController::class, 'validate'])->name('validate');
            Route::delete('/{livraison}', [LivraisonUsineController::class, 'destroy'])->name('destroy');
            Route::get('/download-livraisons-validees', [LivraisonUsineController::class, 'downloadLivraisonsValidees'])->name('download-livraisons-validees');
        });

        // Gestion des Paiements de l'Usine
        Route::prefix('paiements')->name('paiements.')->group(function () {
            Route::get('/', [PaiementController::class, 'index'])->name('index');
            Route::post('/calculer-periode', [PaiementController::class, 'calculerPeriode'])->name('calculer-periode');
            Route::post('/valider-periode', [PaiementController::class, 'validerPeriode'])->name('valider-periode');
            Route::put('/{paiement}/marquer-paye', [PaiementController::class, 'marquerPaye'])->name('marquer-paye');
        });

        // Gestion des Paiements aux Éleveurs
        Route::prefix('paiements-eleveurs')->name('paiements-eleveurs.')->group(function () {
            Route::get('/', [PaiementEleveurController::class, 'index'])->name('index');
            Route::post('/calculer-quinzaine', [PaiementEleveurController::class, 'calculerQuinzaine'])->name('calculer-quinzaine');
            Route::post('/marquer-paye/{membre}', [PaiementEleveurController::class, 'marquerPaye'])->name('marquer-paye');
            Route::post('/marquer-tous-payes', [PaiementEleveurController::class, 'marquerTousPayes'])->name('marquer-tous-payes');
        });
    });

    // Direction Routes
    Route::prefix('direction')->name('direction.')->group(function () {
        
        // Dashboard Direction avec Controller
        Route::get('/dashboard', [DirectionDashboardController::class, 'index'])->name('dashboard');

        // Gestion des Coopératives
        Route::prefix('cooperatives')->name('cooperatives.')->group(function () {
            Route::get('/', [DirectionCooperativeController::class, 'index'])->name('index');
            Route::get('/create', [DirectionCooperativeController::class, 'create'])->name('create');
            Route::post('/', [DirectionCooperativeController::class, 'store'])->name('store');
            Route::get('/{cooperative}', [DirectionCooperativeController::class, 'show'])->name('show');
            Route::get('/{cooperative}/edit', [DirectionCooperativeController::class, 'edit'])->name('edit');
            Route::put('/{cooperative}', [DirectionCooperativeController::class, 'update'])->name('update');
            
            // Actions rapides
            Route::patch('/{cooperative}/activate', [DirectionCooperativeController::class, 'activate'])->name('activate');
            Route::patch('/{cooperative}/deactivate', [DirectionCooperativeController::class, 'deactivate'])->name('deactivate');
            Route::patch('/{cooperative}/remove-responsable', [DirectionCooperativeController::class, 'removeResponsable'])->name('remove-responsable');
            
            // NOUVELLES ROUTES pour téléchargement PDF
            Route::get('/download', [DirectionCooperativeController::class, 'showDownloadForm'])->name('download');
            Route::post('/download', [DirectionCooperativeController::class, 'downloadPDF'])->name('download.pdf');
        });

        // Gestion des Utilisateurs
        Route::prefix('utilisateurs')->name('utilisateurs.')->group(function () {
            Route::get('/', [DirectionUtilisateurController::class, 'index'])->name('index');
            Route::get('/create', [DirectionUtilisateurController::class, 'create'])->name('create');
            Route::post('/', [DirectionUtilisateurController::class, 'store'])->name('store');
            Route::get('/{utilisateur}', [DirectionUtilisateurController::class, 'show'])->name('show');
            Route::get('/{utilisateur}/edit', [DirectionUtilisateurController::class, 'edit'])->name('edit');
            Route::put('/{utilisateur}', [DirectionUtilisateurController::class, 'update'])->name('update');
            
            // Actions rapides
            Route::patch('/{utilisateur}/activate', [DirectionUtilisateurController::class, 'activate'])->name('activate');
            Route::patch('/{utilisateur}/deactivate', [DirectionUtilisateurController::class, 'deactivate'])->name('deactivate');
            
            // Gestion des mots de passe
            Route::get('/{utilisateur}/reset-password', [DirectionUtilisateurController::class, 'showResetPasswordForm'])->name('reset-password');
            Route::put('/{utilisateur}/reset-password', [DirectionUtilisateurController::class, 'resetPassword'])->name('reset-password.update');
            
            // Suppression
            Route::delete('/{utilisateur}', [DirectionUtilisateurController::class, 'destroy'])->name('destroy');
            
            // API pour statistiques
            Route::get('/api/stats', [DirectionUtilisateurController::class, 'getStats'])->name('api.stats');
        });

        // Gestion des Listes Éleveurs
        Route::prefix('eleveurs')->name('eleveurs.')->group(function () {
            Route::get('/download', [DirectionUtilisateurController::class, 'eleveursDownload'])->name('download');
            Route::post('/download', [DirectionUtilisateurController::class, 'downloadEleveurs'])->name('download.process');
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