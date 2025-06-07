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
| Default Route
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});