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
            'matricule.required' => 'رقم التسجيل مطلوب',
            'matricule.string' => 'رقم التسجيل يجب أن يكون نص',
            'matricule.max' => 'رقم التسجيل لا يجب أن يتجاوز 10 أرقام',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.string' => 'كلمة المرور يجب أن تكون نص',
        ]);

        $matricule = $request->input('matricule');
        $password = $request->input('password');

        // Find user by matricule
        $user = Utilisateur::where('matricule', $matricule)->first();

        // Check if user exists
        if (!$user) {
            throw ValidationException::withMessages([
                'matricule' => 'رقم التسجيل غير صحيح',
            ]);
        }

        // Check if user is active
        if (!$user->isActif()) {
            throw ValidationException::withMessages([
                'matricule' => 'الحساب غير مفعل. يرجى الاتصال بالمسؤول',
            ]);
        }

        // Check password
        if (!Hash::check($password, $user->mot_de_passe)) {
            throw ValidationException::withMessages([
                'password' => 'كلمة المرور غير صحيحة',
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
        
        return redirect()->route($route)->with('success', 'مرحباً بك! تم تسجيل الدخول بنجاح');
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'تم تسجيل الخروج بنجاح');
    }

    /**
     * Get the guard to be used during authentication.
     */
    protected function guard()
    {
        return Auth::guard();
    }
}