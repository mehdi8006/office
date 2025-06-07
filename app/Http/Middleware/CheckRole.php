<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->isActif()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Votre compte est inactif. Veuillez contacter l\'administrateur.');
        }

        // Check if user has the required role
        if (!$user->hasRole($role)) {
            // Redirect to appropriate dashboard based on user's actual role
            $redirectRoute = $this->getRedirectRoute($user->role);
            return redirect()->route($redirectRoute)->with('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
        }

        return $next($request);
    }

    /**
     * Get the appropriate redirect route based on user role.
     */
    private function getRedirectRoute(string $role): string
    {
        return match($role) {
            'éleveur' => 'eleveur.dashboard',
            'gestionnaire' => 'gestionnaire.dashboard',
            'usva' => 'usva.dashboard',
            'direction' => 'direction.dashboard',
            default => 'login'
        };
    }
}