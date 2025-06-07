<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GestionnaireMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->isActif()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Compte inactif. Veuillez contacter l\'administrateur.');
        }

        // Check if user has gestionnaire role
        if (!$user->hasRole('gestionnaire')) {
            return redirect()->route('login')->with('error', 'Accès non autorisé.');
        }

        return $next($request);
    }
}