<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors([
                'access' => 'Vous devez être connecté pour accéder à cette page'
            ]);
        }

        $user = Auth::user();
        
        // Check if user account is active
        if (!$user->isActif()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')->withErrors([
                'matricule' => 'Compte inactif. Veuillez contacter l\'administrateur'
            ]);
        }

        // If no specific roles are required, allow access for any authenticated active user
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            // Log unauthorized access attempt
            \Log::warning('Tentative d\'accès non autorisé', [
                'user_id' => $user->id_utilisateur,
                'user_role' => $user->role,
                'required_roles' => $roles,
                'route' => $request->route()->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Redirect to appropriate dashboard based on user's role
            $redirectRoutes = [
                'éleveur' => 'eleveur.dashboard',
                'gestionnaire' => 'gestionnaire.dashboard',
                'usva' => 'usva.dashboard',
                'direction' => 'direction.dashboard',
            ];

            $route = $redirectRoutes[$user->role] ?? 'login';
            
            return redirect()->route($route)->withErrors([
                'access' => 'Vous n\'avez pas l\'autorisation d\'accéder à cette page'
            ])->with('warning', 'Vous avez été redirigé vers votre tableau de bord');
        }

        // Store user role in session for quick access
        session(['user_role' => $user->role]);

        return $next($request);
    }
}