<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\ReceptionLait;
use App\Models\MembreEleveur;
use App\Models\StockLait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceptionController extends Controller
{
    /**
     * Get the cooperative ID for the current gestionnaire.
     */
    private function getCurrentCooperativeId()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'gestionnaire') {
            abort(403, 'Accès non autorisé - Vous devez être gestionnaire');
        }
        
        $cooperativeId = $user->getCooperativeId();
        
        if (!$cooperativeId) {
            return redirect()->route('gestionnaire.dashboard')
                ->with('error', 'Aucune coopérative n\'est assignée à votre compte.')
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
                ->with('error', 'Aucune coopérative n\'est assignée à votre compte.')
                ->send();
        }
        
        return $cooperative;
    }

    /**
     * Display today's receptions with filters and statistics.
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        // Base query for today's receptions only
        $query = ReceptionLait::with(['membre', 'cooperative'])
            ->where('id_cooperative', $cooperativeId)
            ->whereDate('date_reception', today());

        // Filter by member if specified
        if ($request->filled('membre_id')) {
            $query->where('id_membre', $request->membre_id);
        }

        // Search by matricule or member name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('matricule_reception', 'like', "%{$search}%")
                  ->orWhereHas('membre', function ($membreQuery) use ($search) {
                      $membreQuery->where('nom_complet', 'like', "%{$search}%");
                  });
            });
        }

        // Sort by time (most recent first by default)
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $receptions = $query->paginate(20)->withQueryString();

        // Calculate today's statistics
        $stats = $this->calculateTodayStats($cooperativeId);

        // Get active members for filter dropdown
        $membresActifs = MembreEleveur::where('id_cooperative', $cooperativeId)
            ->actif()
            ->orderBy('nom_complet')
            ->get();

        return view('gestionnaire.receptions.index', compact(
            'receptions', 
            'stats', 
            'cooperative', 
            'membresActifs'
        ));
    }

    /**
     * Show the form for creating a new reception.
     */
    public function create()
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        // Get active members for selection
        $membresActifs = MembreEleveur::where('id_cooperative', $cooperativeId)
            ->actif()
            ->orderBy('nom_complet')
            ->get();

        if ($membresActifs->isEmpty()) {
            return redirect()
                ->route('gestionnaire.receptions.index')
                ->with('error', 'Aucun membre actif disponible pour enregistrer une réception.');
        }

        return view('gestionnaire.receptions.create', compact('cooperative', 'membresActifs'));
    }

    /**
     * Store a newly created reception and update stock.
     */
    /**
     * Store a newly created reception and update stock.
     */
    public function store(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        $validated = $request->validate([
            'id_membre' => [
                'required',
                'exists:membres_eleveurs,id_membre',
                function ($attribute, $value, $fail) use ($cooperativeId) {
                    $membre = MembreEleveur::find($value);
                    if (!$membre || $membre->id_cooperative != $cooperativeId || $membre->statut !== 'actif') {
                        $fail('Le membre sélectionné n\'est pas valide ou n\'appartient pas à votre coopérative.');
                    }
                }
            ],
            'quantite_litres' => [
                'required',
                'numeric',
                'min:0.1',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
        ], [
            'id_membre.required' => 'Veuillez sélectionner un membre éleveur',
            'id_membre.exists' => 'Le membre sélectionné n\'existe pas',
            'quantite_litres.required' => 'La quantité est requise',
            'quantite_litres.numeric' => 'La quantité doit être un nombre',
            'quantite_litres.min' => 'La quantité doit être au moins 0.1 litre',
            'quantite_litres.max' => 'La quantité ne peut pas dépasser 9999.99 litres',
            'quantite_litres.regex' => 'La quantité ne peut avoir que 2 décimales maximum',
        ]);

        try {
            DB::beginTransaction();

            // Create the reception
            $reception = ReceptionLait::create([
                'id_cooperative' => $cooperativeId,
                'id_membre' => $validated['id_membre'],
                'date_reception' => today(),
                'quantite_litres' => $validated['quantite_litres'],
                // matricule_reception will be auto-generated by the model
            ]);

            // Update daily stock automatically using the secure method
            StockLait::updateDailyStock($cooperativeId, today());

            DB::commit();

            $membre = MembreEleveur::find($validated['id_membre']);
            
            return redirect()
                ->route('gestionnaire.receptions.index')
                ->with('success', sprintf(
                    'Réception enregistrée avec succès ! Matricule: %s - Membre: %s - Quantité: %s L',
                    $reception->matricule_reception,
                    $membre->nom_complet,
                    number_format($reception->quantite_litres, 2)
                ));
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de l'enregistrement de la réception: " . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement de la réception: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified reception and update stock.
     */
    public function destroy(ReceptionLait $reception)
    {
        // Check if user can access this reception
        $cooperativeId = $this->getCurrentCooperativeId();
        
        if ($reception->id_cooperative != $cooperativeId) {
            abort(403, 'Vous ne pouvez pas supprimer cette réception.');
        }

        // Only allow deletion of today's receptions
        if (!$reception->date_reception->isToday()) {
            return redirect()
                ->back()
                ->with('error', 'Vous ne pouvez supprimer que les réceptions d\'aujourd\'hui.');
        }

        try {
            DB::beginTransaction();

            $matricule = $reception->matricule_reception;
            $membre = $reception->membre->nom_complet;
            $quantite = $reception->quantite_litres;
            
            // Delete the reception
            $reception->delete();

            // Update daily stock
            StockLait::updateDailyStock($cooperativeId, today());

            DB::commit();

            return redirect()
                ->route('gestionnaire.receptions.index')
                ->with('success', sprintf(
                    'Réception supprimée avec succès ! (Matricule: %s - Membre: %s - Quantité: %s L)',
                    $matricule,
                    $membre,
                    number_format($quantite, 2)
                ));
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Calculate today's statistics for the cooperative.
     */
    private function calculateTodayStats($cooperativeId)
    {
        $today = today();
        
        $receptions = ReceptionLait::where('id_cooperative', $cooperativeId)
            ->whereDate('date_reception', $today);

        $stats = [
            'total_receptions' => $receptions->count(),
            'quantite_totale' => $receptions->sum('quantite_litres'),
            'nombre_membres' => $receptions->distinct('id_membre')->count(),
            'quantite_moyenne' => $receptions->avg('quantite_litres') ?: 0,
        ];

        // Get stock information for today
        $stock = StockLait::where('id_cooperative', $cooperativeId)
            ->whereDate('date_stock', $today)
            ->first();

        $stats['stock'] = [
            'quantite_totale' => $stock->quantite_totale ?? 0,
            'quantite_disponible' => $stock->quantite_disponible ?? 0,
            'quantite_livree' => $stock->quantite_livree ?? 0,
        ];

        return $stats;
    }
}