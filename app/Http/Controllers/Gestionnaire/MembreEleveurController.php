<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\MembreEleveur;
use App\Models\Cooperative;
use App\Models\ReceptionLait;
use App\Models\PaiementCooperativeEleveur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class MembreEleveurController extends Controller
{
    /**
     * Get the cooperative ID for the current gestionnaire.
     */
    private function getCurrentCooperativeId()
    {
        $user = Auth::user();
        
        // Vérifier si l'utilisateur est un gestionnaire
        if (!$user || $user->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé - Vous devez être gestionnaire');
        }
        
        $cooperativeId = $user->getCooperativeId();
        
        // Vérifier si le gestionnaire a une coopérative assignée
        if (!$cooperativeId) {
            return redirect()->route('gestionnaire.dashboard')
                ->with('error', 'Aucune coopérative n\'est assignée à votre compte. Contactez l\'administrateur.')
                ->send();
        }
        
        return $cooperativeId;
    }

    /**
     * Get the cooperative for the current gestionnaire.
     */
    private function getCurrentCooperative()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé');
        }
        
        $cooperative = $user->cooperativeGeree;
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                ->with('error', 'Aucune coopérative n\'est assignée à votre compte. Contactez l\'administrateur.')
                ->send();
        }
        
        return $cooperative;
    }

    /**
     * Check if current user can access a membre.
     */
    private function checkAccess($membre)
    {
        if (!Auth::user()->canAccessMembre($membre)) {
            abort(403, 'Vous ne pouvez pas accéder à ce membre.');
        }
    }

    /**
     * Display a listing of membres with filters and pagination.
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        $query = MembreEleveur::with('cooperative')
            ->where('id_cooperative', $cooperativeId);

        // Filter by status
        if ($request->filled('statut') && $request->statut !== 'tous') {
            $query->where('statut', $request->statut);
        }

        // Search by name or CIN
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom_complet', 'like', "%{$search}%")
                  ->orWhere('numero_carte_nationale', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $membres = $query->paginate(15)->withQueryString();

        // Statistics for dashboard cards (only for current cooperative)
        $stats = [
            'total' => MembreEleveur::where('id_cooperative', $cooperativeId)->count(),
            'actifs' => MembreEleveur::where('id_cooperative', $cooperativeId)->actif()->count(),
            'inactifs' => MembreEleveur::where('id_cooperative', $cooperativeId)->inactif()->count(),
            'supprimes' => MembreEleveur::where('id_cooperative', $cooperativeId)->supprime()->count(),
        ];

        return view('gestionnaire.membres.index', compact('membres', 'stats', 'cooperative'));
    }

    /**
     * Show the form for creating a new membre.
     */
    public function create()
    {
        $cooperative = $this->getCurrentCooperative();
        return view('gestionnaire.membres.create', compact('cooperative'));
    }

    /**
     * Store a newly created membre in storage.
     */
    public function store(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        $validated = $request->validate([
            'nom_complet' => 'required|string|max:255',
            'adresse' => 'required|string',
            'telephone' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('membres_eleveurs')->where(function ($query) use ($cooperativeId) {
                    return $query->where('id_cooperative', $cooperativeId);
                })
            ],
            'numero_carte_nationale' => 'required|string|max:20|unique:membres_eleveurs,numero_carte_nationale',
        ], [
            'nom_complet.required' => 'Le nom complet est requis',
            'adresse.required' => 'L\'adresse est requise',
            'telephone.required' => 'Le téléphone est requis',
            'email.required' => 'L\'email est requis',
            'email.email' => 'L\'email doit être valide',
            'email.unique' => 'Cet email existe déjà dans votre coopérative',
            'numero_carte_nationale.required' => 'Le numéro de carte nationale est requis',
            'numero_carte_nationale.unique' => 'Ce numéro de carte nationale existe déjà',
        ]);

        // Forcer l'ID de la coopérative du gestionnaire
        $validated['id_cooperative'] = $cooperativeId;

        try {
            $membre = MembreEleveur::create($validated);
            
            return redirect()
                ->route('gestionnaire.membres.index')
                ->with('success', 'Membre éleveur ajouté avec succès');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'ajout du membre');
        }
    }

    /**
     * Display the specified membre (simplified version).
     */
    public function show(MembreEleveur $membre)
    {
        $this->checkAccess($membre);
        
        $membre->load('cooperative');

        return view('gestionnaire.membres.show', compact('membre'));
    }

    /**
     * Show the form for editing the specified membre.
     */
    public function edit(MembreEleveur $membre)
    {
        $this->checkAccess($membre);
        
        $cooperative = $this->getCurrentCooperative();
        return view('gestionnaire.membres.edit', compact('membre', 'cooperative'));
    }

    /**
     * Update the specified membre in storage.
     */
    public function update(Request $request, MembreEleveur $membre)
    {
        $this->checkAccess($membre);
        
        $cooperativeId = $this->getCurrentCooperativeId();

        $validated = $request->validate([
            'nom_complet' => 'required|string|max:255',
            'adresse' => 'required|string',
            'telephone' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('membres_eleveurs')->where(function ($query) use ($cooperativeId) {
                    return $query->where('id_cooperative', $cooperativeId);
                })->ignore($membre->id_membre, 'id_membre')
            ],
            'numero_carte_nationale' => [
                'required',
                'string',
                'max:20',
                Rule::unique('membres_eleveurs')->ignore($membre->id_membre, 'id_membre')
            ],
        ], [
            'nom_complet.required' => 'Le nom complet est requis',
            'email.unique' => 'Cet email existe déjà dans votre coopérative',
            'numero_carte_nationale.unique' => 'Ce numéro de carte nationale existe déjà',
        ]);

        // S'assurer que la coopérative ne change pas
        $validated['id_cooperative'] = $cooperativeId;

        try {
            $membre->update($validated);
            
            return redirect()
                ->route('gestionnaire.membres.show', $membre)
                ->with('success', 'Membre éleveur modifié avec succès');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification du membre');
        }
    }

    /**
     * Activate the specified membre.
     */
    public function activate(MembreEleveur $membre)
    {
        $this->checkAccess($membre);
        
        try {
            $membre->activer();
            
            return redirect()->back()->with('success', 'Membre activé avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'activation');
        }
    }

    /**
     * Deactivate the specified membre.
     */
    public function deactivate(MembreEleveur $membre)
    {
        $this->checkAccess($membre);
        
        try {
            $membre->desactiver();
            
            return redirect()->back()->with('success', 'Membre désactivé avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la désactivation');
        }
    }

    /**
     * Remove the specified membre from storage.
     */
    public function destroy(Request $request, MembreEleveur $membre)
    {
        $this->checkAccess($membre);
        
        $request->validate([
            'raison_suppression' => 'required|string|max:500'
        ], [
            'raison_suppression.required' => 'La raison de suppression est requise'
        ]);

        try {
            $membre->supprimer($request->raison_suppression);
            
            return redirect()
                ->route('gestionnaire.membres.index')
                ->with('success', 'Membre supprimé avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression');
        }
    }

    /**
     * Restore the specified deleted membre.
     */
    public function restore(MembreEleveur $membre)
    {
        $this->checkAccess($membre);
        
        // Vérifier que le membre est bien supprimé
        if ($membre->statut !== 'suppression') {
            return redirect()->back()->with('error', 'Ce membre n\'est pas supprimé');
        }

        try {
            $membre->restaurer();
            
            return redirect()
                ->back()
                ->with('success', 'Membre restauré avec succès et réactivé');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la restauration : ' . $e->getMessage());
        }
    }

    /**
     * Download membre receptions history as PDF.
     */
    public function downloadReceptions(MembreEleveur $membre)
    {
        $this->checkAccess($membre);
        
        // Get all receptions for this membre
        $receptions = ReceptionLait::where('id_membre', $membre->id_membre)
            ->with('cooperative')
            ->latest('date_reception')
            ->get();

        // Calculate statistics
        $stats = [
            'total_receptions' => $receptions->count(),
            'total_quantite' => $receptions->sum('quantite_litres'),
            'moyenne_quantite' => $receptions->avg('quantite_litres') ?: 0,
            'premiere_reception' => $receptions->last()?->date_reception,
            'derniere_reception' => $receptions->first()?->date_reception,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('gestionnaire.membres.exports.receptions-pdf', compact('membre', 'receptions', 'stats'));
        
        $filename = 'Historique_Receptions_' . str_replace(' ', '_', $membre->nom_complet) . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Download membre payments history as PDF.
     */
    public function downloadPaiements(MembreEleveur $membre)
    {
        $this->checkAccess($membre);
        
        // Get all payments for this membre
        $paiements = PaiementCooperativeEleveur::where('id_membre', $membre->id_membre)
            ->with('cooperative')
            ->latest('periode_fin')
            ->get();

        // Calculate statistics
        $stats = [
            'total_paiements' => $paiements->count(),
            'montant_total_paye' => $paiements->where('statut', 'paye')->sum('montant_total'),
            'montant_total_en_attente' => $paiements->where('statut', 'calcule')->sum('montant_total'),
            'quantite_totale' => $paiements->sum('quantite_totale'),
            'prix_moyen' => $paiements->avg('prix_unitaire') ?: 0,
            'premier_paiement' => $paiements->last()?->periode_debut,
            'dernier_paiement' => $paiements->first()?->periode_fin,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('gestionnaire.membres.exports.paiements-pdf', compact('membre', 'paiements', 'stats'));
        
        $filename = 'Historique_Paiements_' . str_replace(' ', '_', $membre->nom_complet) . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}