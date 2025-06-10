<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Cooperative;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CooperativeController extends Controller
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
     * Display a listing of cooperatives.
     */
    public function index(Request $request)
    {
        $this->checkDirectionAccess();

        $query = Cooperative::with(['responsable', 'membresActifs']);

        // Filtrage par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Recherche par nom ou matricule
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom_cooperative', 'like', '%' . $search . '%')
                  ->orWhere('matricule', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filtrage par responsable
        if ($request->filled('responsable')) {
            $query->where('responsable_id', $request->responsable);
        }

        $cooperatives = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Récupérer tous les gestionnaires pour le filtre
        $gestionnaires = Utilisateur::where('role', 'gestionnaire')
                                   ->where('statut', 'actif')
                                   ->orderBy('nom_complet')
                                   ->get();

        return view('direction.cooperatives.index', compact('cooperatives', 'gestionnaires'));
    }

    /**
     * Show the form for creating a new cooperative.
     */
    public function create()
    {
        $this->checkDirectionAccess();

        // Récupérer les gestionnaires disponibles (sans coopérative assignée)
        $gestionnaires = Utilisateur::where('role', 'gestionnaire')
                                   ->where('statut', 'actif')
                                   ->whereDoesntHave('cooperativeGeree')
                                   ->orderBy('nom_complet')
                                   ->get();

        return view('direction.cooperatives.create', compact('gestionnaires'));
    }

    /**
     * Store a newly created cooperative in storage.
     */
    public function store(Request $request)
    {
        $this->checkDirectionAccess();

        $request->validate([
            'nom_cooperative' => 'required|string|max:255',
            'adresse' => 'required|string|max:500',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|unique:cooperatives,email',
            'responsable_id' => 'nullable|exists:utilisateurs,id_utilisateur',
            'statut' => 'required|in:actif,inactif'
        ]);

        // Vérifier que le gestionnaire sélectionné n'a pas déjà une coopérative
        if ($request->responsable_id) {
            $gestionnaire = Utilisateur::find($request->responsable_id);
            if ($gestionnaire->role !== 'gestionnaire') {
                return back()->withErrors(['responsable_id' => 'L\'utilisateur sélectionné doit avoir le rôle gestionnaire.']);
            }
            
            if ($gestionnaire->cooperativeGeree) {
                return back()->withErrors(['responsable_id' => 'Ce gestionnaire gère déjà une autre coopérative.']);
            }
        }

        Cooperative::create($request->all());

        return redirect()->route('direction.cooperatives.index')
                        ->with('success', 'Coopérative créée avec succès.');
    }

    /**
     * Display the specified cooperative.
     */
    public function show(Cooperative $cooperative)
    {
        $this->checkDirectionAccess();

        $cooperative->load(['responsable', 'membres', 'membresActifs', 'membresInactifs']);
        
        return view('direction.cooperatives.show', compact('cooperative'));
    }

    /**
     * Show the form for editing the specified cooperative.
     */
    public function edit(Cooperative $cooperative)
    {
        $this->checkDirectionAccess();

        // Récupérer les gestionnaires disponibles + le gestionnaire actuel s'il existe
        $gestionnaires = Utilisateur::where('role', 'gestionnaire')
                                   ->where('statut', 'actif')
                                   ->where(function($query) use ($cooperative) {
                                       $query->whereDoesntHave('cooperativeGeree')
                                             ->orWhere('id_utilisateur', $cooperative->responsable_id);
                                   })
                                   ->orderBy('nom_complet')
                                   ->get();

        return view('direction.cooperatives.edit', compact('cooperative', 'gestionnaires'));
    }

    /**
     * Update the specified cooperative in storage.
     */
    public function update(Request $request, Cooperative $cooperative)
    {
        $this->checkDirectionAccess();

        $request->validate([
            'nom_cooperative' => 'required|string|max:255',
            'adresse' => 'required|string|max:500',
            'telephone' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                Rule::unique('cooperatives')->ignore($cooperative->id_cooperative, 'id_cooperative')
            ],
            'responsable_id' => 'nullable|exists:utilisateurs,id_utilisateur',
            'statut' => 'required|in:actif,inactif'
        ]);

        // Vérifier que le gestionnaire sélectionné n'a pas déjà une coopérative
        if ($request->responsable_id && $request->responsable_id != $cooperative->responsable_id) {
            $gestionnaire = Utilisateur::find($request->responsable_id);
            if ($gestionnaire->role !== 'gestionnaire') {
                return back()->withErrors(['responsable_id' => 'L\'utilisateur sélectionné doit avoir le rôle gestionnaire.']);
            }
            
            if ($gestionnaire->cooperativeGeree) {
                return back()->withErrors(['responsable_id' => 'Ce gestionnaire gère déjà une autre coopérative.']);
            }
        }

        $cooperative->update($request->all());

        return redirect()->route('direction.cooperatives.index')
                        ->with('success', 'Coopérative mise à jour avec succès.');
    }

    /**
     * Activate the specified cooperative.
     */
    public function activate(Cooperative $cooperative)
    {
        $this->checkDirectionAccess();

        $cooperative->update(['statut' => 'actif']);

        return redirect()->back()->with('success', 'Coopérative activée avec succès.');
    }

    /**
     * Deactivate the specified cooperative.
     */
    public function deactivate(Cooperative $cooperative)
    {
        $this->checkDirectionAccess();

        $cooperative->update(['statut' => 'inactif']);

        return redirect()->back()->with('success', 'Coopérative désactivée avec succès.');
    }

    /**
     * Remove responsable from cooperative.
     */
    public function removeResponsable(Cooperative $cooperative)
    {
        $this->checkDirectionAccess();

        $cooperative->update(['responsable_id' => null]);

        return redirect()->back()->with('success', 'Responsable retiré de la coopérative avec succès.');
    }
}