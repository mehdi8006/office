<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login form submission.
     */
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'matricule' => 'required|string|min:1|max:10',
            'password' => 'required|string|min:1',
        ], [
            'matricule.required' => 'Le numéro d\'inscription est requis',
            'matricule.string' => 'Le numéro d\'inscription doit être un texte',
            'matricule.max' => 'Le numéro d\'inscription ne doit pas dépasser 10 caractères',
            'password.required' => 'Le mot de passe est requis',
            'password.string' => 'Le mot de passe doit être un texte',
        ]);

        $matricule = $request->input('matricule');
        $password = $request->input('password');

        // Find user by matricule
        $user = Utilisateur::where('matricule', $matricule)->first();

        // Check if user exists
        if (!$user) {
            throw ValidationException::withMessages([
                'matricule' => 'Numéro d\'inscription incorrect',
            ]);
        }

        // Check if user is active
        if (!$user->isActif()) {
            throw ValidationException::withMessages([
                'matricule' => 'Compte inactif. Veuillez contacter l\'administrateur',
            ]);
        }

        // Check password
        if (!Hash::check($password, $user->mot_de_passe)) {
            throw ValidationException::withMessages([
                'password' => 'Mot de passe incorrect',
            ]);
        }

        // Login the user
        Auth::login($user, $request->filled('remember'));

        // Regenerate session to prevent session fixation attacks
        $request->session()->regenerate();

        // Redirect based on user role
        return $this->redirectBasedOnRole($user->role);
    }

    /**
     * Redirect user based on their role.
     */
    private function redirectBasedOnRole($role)
    {
        $redirectRoutes = [
            'éleveur' => 'eleveur.dashboard',
            'gestionnaire' => 'gestionnaire.dashboard',
            'usva' => 'usva.dashboard',
            'direction' => 'direction.dashboard',
        ];

        $route = $redirectRoutes[$role] ?? 'eleveur.dashboard';
        
        return redirect()->route($route)->with('success', 'Bienvenue ! Connexion réussie');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Déconnexion réussie');
    }

    /**
     * Get the guard to be used during authentication.
     */
    protected function guard()
    {
        return Auth::guard();
    }
}