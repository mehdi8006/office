<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\LivraisonUsine;
use App\Models\StockLait;
use App\Models\PaiementCooperativeUsine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LivraisonUsineController extends Controller
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
     * Check if current user can access a livraison.
     */
    private function checkAccess($livraison)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        if ($livraison->id_cooperative != $cooperativeId) {
            abort(403, 'Vous ne pouvez pas accéder à cette livraison.');
        }
    }

    /**
     * Display a listing of livraisons with filters and pagination.
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        $query = LivraisonUsine::with('cooperative')
            ->where('id_cooperative', $cooperativeId);

        // Filter by date range
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_livraison', [$request->date_debut, $request->date_fin]);
        } elseif ($request->filled('date_debut')) {
            $query->whereDate('date_livraison', '>=', $request->date_debut);
        } elseif ($request->filled('date_fin')) {
            $query->whereDate('date_livraison', '<=', $request->date_fin);
        } else {
            // Par défaut, afficher les 30 derniers jours
            $query->whereDate('date_livraison', '>=', now()->subDays(30));
        }

        // Filter by status
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date_livraison');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $livraisons = $query->paginate(15)->withQueryString();

        // Calculate statistics
        $stats = $this->calculateLivraisonStats($cooperativeId, $request);

        return view('gestionnaire.livraisons.index', compact('livraisons', 'stats', 'cooperative'));
    }

    /**
     * Show the form for creating a new livraison.
     */
    public function create(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        // Get date from request or default to today
        $selectedDate = $request->get('date', today()->format('Y-m-d'));
        
        try {
            $date = Carbon::createFromFormat('Y-m-d', $selectedDate);
        } catch (\Exception $e) {
            $date = today();
        }

        // Get or create stock for this date
        $stock = StockLait::firstOrCreate([
            'id_cooperative' => $cooperativeId,
            'date_stock' => $date
        ], [
            'quantite_totale' => 0,
            'quantite_disponible' => 0,
            'quantite_livree' => 0
        ]);

        // Update stock if needed
        if ($stock->quantite_totale == 0) {
            StockLait::updateDailyStock($cooperativeId, $date);
            $stock->refresh();
        }

        // Check if there's available stock
        if ($stock->quantite_disponible <= 0) {
            return redirect()
                ->route('gestionnaire.stock.show', $date->format('Y-m-d'))
                ->with('error', 'Aucun stock disponible pour cette date.');
        }

        // Get existing livraisons for this date
        $existingLivraisons = LivraisonUsine::where('id_cooperative', $cooperativeId)
            ->whereDate('date_livraison', $date)
            ->get();

        return view('gestionnaire.livraisons.create', compact('stock', 'existingLivraisons', 'cooperative'));
    }

    /**
     * Store a newly created livraison and update stock.
     */
    public function store(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        $validated = $request->validate([
            'date_livraison' => 'required|date',
            'quantite_litres' => [
                'required',
                'numeric',
                'min:0.1',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'prix_unitaire' => [
                'required',
                'numeric',
                'min:0.1',
                'max:999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
        ], [
            'date_livraison.required' => 'La date de livraison est requise',
            'date_livraison.date' => 'Format de date invalide',
            'quantite_litres.required' => 'La quantité est requise',
            'quantite_litres.numeric' => 'La quantité doit être un nombre',
            'quantite_litres.min' => 'La quantité doit être au moins 0.1 litre',
            'quantite_litres.max' => 'La quantité ne peut pas dépasser 9999.99 litres',
            'quantite_litres.regex' => 'La quantité ne peut avoir que 2 décimales maximum',
            'prix_unitaire.required' => 'Le prix unitaire est requis',
            'prix_unitaire.numeric' => 'Le prix unitaire doit être un nombre',
            'prix_unitaire.min' => 'Le prix unitaire doit être au moins 0.1 DH',
            'prix_unitaire.max' => 'Le prix unitaire ne peut pas dépasser 999.99 DH',
            'prix_unitaire.regex' => 'Le prix unitaire ne peut avoir que 2 décimales maximum',
        ]);

        try {
            DB::beginTransaction();

            // Get stock for this date
            $stock = StockLait::where('id_cooperative', $cooperativeId)
                ->whereDate('date_stock', $validated['date_livraison'])
                ->first();

            if (!$stock) {
                return back()
                    ->withInput()
                    ->with('error', 'Aucun stock trouvé pour cette date.');
            }

            // Check if enough stock is available
            if ($validated['quantite_litres'] > $stock->quantite_disponible) {
                return back()
                    ->withInput()
                    ->with('error', sprintf(
                        'Quantité insuffisante. Stock disponible: %s L',
                        number_format($stock->quantite_disponible, 2)
                    ));
            }

            // Create the livraison
            $livraison = LivraisonUsine::create([
                'id_cooperative' => $cooperativeId,
                'date_livraison' => $validated['date_livraison'],
                'quantite_litres' => $validated['quantite_litres'],
                'prix_unitaire' => $validated['prix_unitaire'],
                'statut' => 'planifiee',
                // montant_total will be auto-calculated by the model
            ]);

            // Update stock (reduce available quantity)
            $stock->livrer($validated['quantite_litres']);

            DB::commit();

            return redirect()
                ->route('gestionnaire.livraisons.index')
                ->with('success', sprintf(
                    'Livraison créée avec succès ! Quantité: %s L - Montant: %s DH',
                    number_format($livraison->quantite_litres, 2),
                    number_format($livraison->montant_total, 2)
                ));
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la livraison: ' . $e->getMessage());
        }
    }

    /**
     * Validate a livraison (change status from planifiee to validee).
     */
    public function validate(LivraisonUsine $livraison)
    {
        $this->checkAccess($livraison);
        
        if ($livraison->statut !== 'planifiee') {
            return redirect()
                ->back()
                ->with('error', 'Cette livraison ne peut pas être validée (statut: ' . $livraison->statut_label . ')');
        }

        try {
            $livraison->valider();
            
            return redirect()
                ->back()
                ->with('success', 'Livraison validée avec succès');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified livraison and restore stock.
     */
    public function destroy(LivraisonUsine $livraison)
    {
        $this->checkAccess($livraison);
        
        // Only allow deletion of planned livraisons
        if ($livraison->statut !== 'planifiee') {
            return redirect()
                ->back()
                ->with('error', 'Seules les livraisons planifiées peuvent être supprimées');
        }

        try {
            DB::beginTransaction();

            $quantite = $livraison->quantite_litres;
            $montant = $livraison->montant_total;
            
            // Get stock for this date
            $stock = StockLait::where('id_cooperative', $livraison->id_cooperative)
                ->whereDate('date_stock', $livraison->date_livraison)
                ->first();

            // Delete the livraison
            $livraison->delete();

            // Restore stock if it exists
            if ($stock) {
                $stock->annulerLivraison($quantite);
            }

            DB::commit();

            return redirect()
                ->route('gestionnaire.livraisons.index')
                ->with('success', sprintf(
                    'Livraison supprimée avec succès ! (Quantité: %s L - Montant: %s DH)',
                    number_format($quantite, 2),
                    number_format($montant, 2)
                ));
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics for the livraisons listing.
     */
    private function calculateLivraisonStats($cooperativeId, $request)
    {
        $query = LivraisonUsine::where('id_cooperative', $cooperativeId);

        // Apply same filters as main query
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_livraison', [$request->date_debut, $request->date_fin]);
        } elseif ($request->filled('date_debut')) {
            $query->whereDate('date_livraison', '>=', $request->date_debut);
        } elseif ($request->filled('date_fin')) {
            $query->whereDate('date_livraison', '<=', $request->date_fin);
        } else {
            $query->whereDate('date_livraison', '>=', now()->subDays(30));
        }

        $totalLivraisons = $query->count();
        $totalQuantite = $query->sum('quantite_litres');
        $totalMontant = $query->sum('montant_total');
        $moyennePrix = $query->avg('prix_unitaire') ?: 0;

        // Count by status
        $statsQuery = clone $query;
        $planifiees = $statsQuery->where('statut', 'planifiee')->count();
        
        $statsQuery = clone $query;
        $validees = $statsQuery->where('statut', 'validee')->count();
        
        $statsQuery = clone $query;
        $payees = $statsQuery->where('statut', 'payee')->count();

        return [
            'total_livraisons' => $totalLivraisons,
            'total_quantite' => $totalQuantite,
            'total_montant' => $totalMontant,
            'moyenne_prix' => $moyennePrix,
            'livraisons_planifiees' => $planifiees,
            'livraisons_validees' => $validees,
            'livraisons_payees' => $payees,
        ];
    }
}