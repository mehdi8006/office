<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UtilisateurController extends Controller
{
    /**
     * Vérifier les permissions pour la direction
     */
    private function checkDirectionAccess()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'direction') {
            abort(403, 'Accès non autorisé. Seule la direction peut accéder à cette section.');
        }

        if (!$user->isActif()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Votre compte est inactif.');
        }
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->checkDirectionAccess();

        $query = Utilisateur::query();

        // Filtrage par rôle
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtrage par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Recherche par nom, email ou matricule
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_complet', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('matricule', 'like', '%' . $search . '%');
            });
        }

        $utilisateurs = $query->orderBy('created_at', 'desc')->paginate(15);

        // Statistiques pour les filtres
        $stats = [
            'total' => Utilisateur::count(),
            'actifs' => Utilisateur::where('statut', 'actif')->count(),
            'inactifs' => Utilisateur::where('statut', 'inactif')->count(),
            'direction' => Utilisateur::where('role', 'direction')->count(),
            'gestionnaires' => Utilisateur::where('role', 'gestionnaire')->count(),
            'usva' => Utilisateur::where('role', 'usva')->count(),
        ];

        return view('direction.utilisateurs.index', compact('utilisateurs', 'stats'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->checkDirectionAccess();

        $roles = ['direction', 'gestionnaire', 'usva'];
        
        return view('direction.utilisateurs.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->checkDirectionAccess();

        $request->validate([
            'nom_complet' => 'required|string|max:255',
            'email' => 'required|email|unique:utilisateurs,email',
            'telephone' => 'required|string|max:20',
            'role' => 'required|in:direction,gestionnaire,usva',
            'statut' => 'required|in:actif,inactif',
            'mot_de_passe' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Créer l'utilisateur
        $utilisateur = Utilisateur::create([
            'nom_complet' => $request->nom_complet,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'role' => $request->role,
            'statut' => $request->statut,
            'mot_de_passe' => Hash::make($request->mot_de_passe),
        ]);

        return redirect()->route('direction.utilisateurs.index')
                        ->with('success', 'Utilisateur créé avec succès. Matricule: ' . $utilisateur->matricule);
    }

    /**
     * Display the specified user.
     */
    public function show(Utilisateur $utilisateur)
    {
        $this->checkDirectionAccess();

        // Charger les relations selon le rôle
        $relations = [];
        if ($utilisateur->role === 'gestionnaire') {
            $relations[] = 'cooperativeGeree';
        }

        if (!empty($relations)) {
            $utilisateur->load($relations);
        }

        // Statistiques spécifiques selon le rôle
        $stats = [];
        if ($utilisateur->role === 'gestionnaire' && $utilisateur->cooperativeGeree) {
            $cooperative = $utilisateur->cooperativeGeree;
            $stats = [
                'membres_total' => $cooperative->membres()->count(),
                'membres_actifs' => $cooperative->membresActifs()->count(),
                'cooperative_nom' => $cooperative->nom_cooperative,
                'cooperative_statut' => $cooperative->statut,
            ];
        }

        return view('direction.utilisateurs.show', compact('utilisateur', 'stats'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Utilisateur $utilisateur)
    {
        $this->checkDirectionAccess();

        // Empêcher l'auto-modification du compte direction connecté
        if ($utilisateur->id_utilisateur === Auth::id()) {
            return redirect()->back()->with('warning', 'Vous ne pouvez pas modifier votre propre compte.');
        }

        $roles = ['direction', 'gestionnaire', 'usva', 'éleveur'];
        
        return view('direction.utilisateurs.edit', compact('utilisateur', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, Utilisateur $utilisateur)
    {
        $this->checkDirectionAccess();

        // Empêcher l'auto-modification du compte direction connecté
        if ($utilisateur->id_utilisateur === Auth::id()) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas modifier votre propre compte.');
        }

        $request->validate([
            'nom_complet' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('utilisateurs')->ignore($utilisateur->id_utilisateur, 'id_utilisateur')
            ],
            'telephone' => 'required|string|max:20',
            'role' => 'required|in:direction,gestionnaire,usva',
            'statut' => 'required|in:actif,inactif',
        ]);

        // Vérifier les changements de rôle critiques
        if ($utilisateur->role === 'gestionnaire' && $request->role !== 'gestionnaire') {
            // Vérifier si le gestionnaire a une coopérative assignée
            if ($utilisateur->cooperativeGeree) {
                return redirect()->back()
                                ->with('error', 'Impossible de changer le rôle : ce gestionnaire gère la coopérative "' . $utilisateur->cooperativeGeree->nom_cooperative . '". Retirez-le d\'abord de sa coopérative.');
            }
        }

        $utilisateur->update($request->only(['nom_complet', 'email', 'telephone', 'role', 'statut']));

        return redirect()->route('direction.utilisateurs.index')
                        ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Activate the specified user.
     */
    public function activate(Utilisateur $utilisateur)
    {
        $this->checkDirectionAccess();

        $utilisateur->update(['statut' => 'actif']);

        return redirect()->back()->with('success', 'Utilisateur activé avec succès.');
    }

    /**
     * Deactivate the specified user.
     */
    public function deactivate(Utilisateur $utilisateur)
    {
        $this->checkDirectionAccess();

        // Empêcher la désactivation de son propre compte
        if ($utilisateur->id_utilisateur === Auth::id()) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        // Vérifier si c'est un gestionnaire avec coopérative
        if ($utilisateur->role === 'gestionnaire' && $utilisateur->cooperativeGeree) {
            return redirect()->back()
                            ->with('warning', 'Attention : ce gestionnaire gère la coopérative "' . $utilisateur->cooperativeGeree->nom_cooperative . '". Considérez retirer son assignation d\'abord.');
        }

        $utilisateur->update(['statut' => 'inactif']);

        return redirect()->back()->with('success', 'Utilisateur désactivé avec succès.');
    }

    /**
     * Show password reset form.
     */
    public function showResetPasswordForm(Utilisateur $utilisateur)
    {
        $this->checkDirectionAccess();

        return view('direction.utilisateurs.reset-password', compact('utilisateur'));
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, Utilisateur $utilisateur)
    {
        $this->checkDirectionAccess();

        $request->validate([
            'mot_de_passe' => ['required', 'confirmed', Password::min(8)],
        ]);

        $utilisateur->update([
            'mot_de_passe' => Hash::make($request->mot_de_passe)
        ]);

        return redirect()->route('direction.utilisateurs.show', $utilisateur)
                        ->with('success', 'Mot de passe réinitialisé avec succès.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Utilisateur $utilisateur)
    {
        $this->checkDirectionAccess();

        // Empêcher l'auto-suppression
        if ($utilisateur->id_utilisateur === Auth::id()) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Vérifier si c'est un gestionnaire avec coopérative
        if ($utilisateur->role === 'gestionnaire' && $utilisateur->cooperativeGeree) {
            return redirect()->back()
                            ->with('error', 'Impossible de supprimer : ce gestionnaire gère la coopérative "' . $utilisateur->cooperativeGeree->nom_cooperative . '". Retirez-le d\'abord de sa coopérative.');
        }

        $nom = $utilisateur->nom_complet;
        $utilisateur->delete();

        return redirect()->route('direction.utilisateurs.index')
                        ->with('success', 'Utilisateur "' . $nom . '" supprimé avec succès.');
    }

    /**
     * Get users statistics for dashboard
     */
    public function getStats()
    {
        $this->checkDirectionAccess();

        return response()->json([
            'total' => Utilisateur::count(),
            'actifs' => Utilisateur::where('statut', 'actif')->count(),
            'inactifs' => Utilisateur::where('statut', 'inactif')->count(),
            'par_role' => [
                'direction' => Utilisateur::where('role', 'direction')->count(),
                'gestionnaires' => Utilisateur::where('role', 'gestionnaire')->count(),
                'usva' => Utilisateur::where('role', 'usva')->count(),
            ]
        ]);
    }
}