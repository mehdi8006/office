<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Cooperative;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /**
     * Show download form for cooperatives PDF.
     */
    public function showDownloadForm()
    {
        $this->checkDirectionAccess();

        // Récupérer tous les gestionnaires pour le filtre
        $gestionnaires = Utilisateur::where('role', 'gestionnaire')
                                   ->where('statut', 'actif')
                                   ->orderBy('nom_complet')
                                   ->get();

        // Statistiques des coopératives
        $stats = [
            'total_cooperatives' => Cooperative::count(),
            'cooperatives_actives' => Cooperative::where('statut', 'actif')->count(),
            'cooperatives_inactives' => Cooperative::where('statut', 'inactif')->count(),
            'avec_responsable' => Cooperative::whereNotNull('responsable_id')->count(),
            'sans_responsable' => Cooperative::whereNull('responsable_id')->count(),
        ];

        return view('direction.cooperatives.download', compact('gestionnaires', 'stats'));
    }

    /**
     * Download cooperatives list as PDF.
     */
    public function downloadPDF(Request $request)
    {
        $this->checkDirectionAccess();

        $request->validate([
            'statut' => 'nullable|in:actif,inactif',
            'responsable_filter' => 'nullable|in:avec,sans',
            'responsable_id' => 'nullable|exists:utilisateurs,id_utilisateur',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'include_membres' => 'nullable|boolean',
        ]);

        // Construire la requête
        $query = Cooperative::with(['responsable', 'membresActifs']);

        // Filtres
        if ($request->statut) {
            $query->where('statut', $request->statut);
        }

        if ($request->responsable_filter) {
            if ($request->responsable_filter === 'avec') {
                $query->whereNotNull('responsable_id');
            } else {
                $query->whereNull('responsable_id');
            }
        }

        if ($request->responsable_id) {
            $query->where('responsable_id', $request->responsable_id);
        }

        if ($request->date_debut && $request->date_fin) {
            $query->whereBetween('created_at', [$request->date_debut, $request->date_fin]);
        }

        $cooperatives = $query->orderBy('nom_cooperative')->get();

        // Préparer les données pour le PDF
        $data = [
            'cooperatives' => $cooperatives,
            'filtres' => [
                'statut' => $request->statut,
                'responsable_filter' => $request->responsable_filter,
                'responsable_nom' => $request->responsable_id ? 
                    Utilisateur::find($request->responsable_id)->nom_complet : null,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'include_membres' => $request->boolean('include_membres'),
            ],
            'stats' => [
                'total' => $cooperatives->count(),
                'actives' => $cooperatives->where('statut', 'actif')->count(),
                'inactives' => $cooperatives->where('statut', 'inactif')->count(),
                'avec_responsable' => $cooperatives->whereNotNull('responsable_id')->count(),
                'sans_responsable' => $cooperatives->whereNull('responsable_id')->count(),
                'total_membres' => $cooperatives->sum(function ($coop) {
                    return $coop->membresActifs->count();
                }),
            ],
            'generated_at' => now(),
            'generated_by' => Auth::user(),
        ];

        // Générer le PDF
        $pdf = PDF::loadView('direction.cooperatives.pdf-template', $data);
        
        // Nom du fichier
        $filename = 'cooperatives_' . date('Y-m-d_H-i-s') . '.pdf';

        // Configuration du PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'Arial'
        ]);

        return $pdf->download($filename);
    }
}