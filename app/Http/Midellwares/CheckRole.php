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
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user is active
        if (!$user->isActif()) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'matricule' => 'الحساب غير مفعل. يرجى الاتصال بالمسؤول'
            ]);
        }

        // If no specific roles are required, allow access
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            // Redirect to appropriate dashboard based on user's role
            $redirectRoutes = [
                'éleveur' => 'eleveur.dashboard',
                'gestionnaire' => 'gestionnaire.dashboard',
                'usva' => 'usva.dashboard',
                'direction' => 'direction.dashboard',
            ];

            $route = $redirectRoutes[$user->role] ?? 'eleveur.dashboard';
            
            return redirect()->route($route)->withErrors([
                'access' => 'ليس لديك صلاحية للوصول إلى هذه الصفحة'
            ]);
        }

        return $next($request);
    }
}