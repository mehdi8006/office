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
     * Show the form for creating a new livraison - SIMPLIFIED VERSION.
     */
    public function create(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        try {
            // Calculate total available stock for the entire cooperative
            $stockTotalCooperative = StockLait::where('id_cooperative', $cooperativeId)
                ->sum('quantite_disponible');

            // Check if there's available stock
            if ($stockTotalCooperative <= 0) {
                return redirect()
                    ->route('gestionnaire.stock.index')
                    ->with('error', 'Aucun stock disponible pour votre coopérative.');
            }

            return view('gestionnaire.livraisons.create', compact('stockTotalCooperative', 'cooperative'));

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la création de livraison: " . $e->getMessage());
            
            return redirect()
                ->route('gestionnaire.stock.index')
                ->with('error', 'Erreur lors de la récupération du stock.');
        }
    }

    /**
     * Store a newly created livraison and update stock - SIMPLIFIED VERSION.
     */
    public function store(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        // Get current available stock for validation
        $stockTotalDisponible = StockLait::where('id_cooperative', $cooperativeId)
            ->sum('quantite_disponible');
        
        $validated = $request->validate([
            'date_livraison' => 'required|date',
            'quantite_litres' => [
                'required',
                'numeric',
                'min:0.1',
                'max:9999.99',
                function ($attribute, $value, $fail) use ($stockTotalDisponible) {
                    if ($value > $stockTotalDisponible) {
                        $fail("La quantité ne peut pas dépasser le stock disponible (" . number_format($stockTotalDisponible, 2) . " L)");
                    }
                },
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
        ]);

        try {
            DB::beginTransaction();

            // Double-check stock availability (in case it changed between form load and submit)
            $stockTotalDisponibleActuel = StockLait::where('id_cooperative', $cooperativeId)
                ->sum('quantite_disponible');

            if ($validated['quantite_litres'] > $stockTotalDisponibleActuel) {
                return back()
                    ->withInput()
                    ->with('error', sprintf(
                        'Quantité insuffisante. Stock total disponible: %s L',
                        number_format($stockTotalDisponibleActuel, 2)
                    ));
            }

            // Normaliser la date
            $dateNormalisee = Carbon::parse($validated['date_livraison'])->format('Y-m-d');

            // Create the livraison (montant calculé automatiquement via accessor)
            $livraison = LivraisonUsine::create([
                'id_cooperative' => $cooperativeId,
                'date_livraison' => $dateNormalisee,
                'quantite_litres' => $validated['quantite_litres'],
                'statut' => 'planifiee',
                // montant_total is now calculated automatically via accessor
            ]);

            // Update stocks by reducing available quantities intelligently
            $this->reduceAvailableStock($cooperativeId, $validated['quantite_litres']);

            DB::commit();

            return redirect()
                ->route('gestionnaire.livraisons.index')
                ->with('success', sprintf(
                    'Livraison créée avec succès ! Quantité: %s L',
                    number_format($livraison->quantite_litres, 2)
                ));
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de la création de livraison: " . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la livraison: ' . $e->getMessage());
        }
    }

    /**
     * Intelligently reduce available stock from multiple dates.
     */
    private function reduceAvailableStock($cooperativeId, $quantiteALivrer)
    {
        // Get stocks with available quantity, ordered by date (oldest first = FIFO)
        $stocks = StockLait::where('id_cooperative', $cooperativeId)
            ->where('quantite_disponible', '>', 0)
            ->orderBy('date_stock', 'asc')
            ->get();

        $quantiteRestante = $quantiteALivrer;

        foreach ($stocks as $stock) {
            if ($quantiteRestante <= 0) {
                break;
            }

            $quantiteAPrendre = min($quantiteRestante, $stock->quantite_disponible);
            
            // Use the existing livrer method from StockLait model
            $stock->livrer($quantiteAPrendre);
            
            $quantiteRestante -= $quantiteAPrendre;
        }

        if ($quantiteRestante > 0) {
            throw new \Exception('Stock insuffisant pour livrer la quantité demandée');
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
            $dateNormalisee = $livraison->date_livraison->format('Y-m-d');
            
            // Delete the livraison first
            $livraison->delete();

            // Restore stock intelligently (add back to the most recent available stock)
            $this->restoreAvailableStock($livraison->id_cooperative, $quantite, $dateNormalisee);

            DB::commit();

            return redirect()
                ->route('gestionnaire.livraisons.index')
                ->with('success', sprintf(
                    'Livraison supprimée avec succès ! (Quantité: %s L)',
                    number_format($quantite, 2)
                ));
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de la suppression de livraison: " . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Restore stock when a livraison is cancelled.
     */
    private function restoreAvailableStock($cooperativeId, $quantiteARestaurer, $dateLivraison)
    {
        // Try to restore to the original date first
        $stockOriginal = StockLait::where('id_cooperative', $cooperativeId)
            ->whereDate('date_stock', $dateLivraison)
            ->first();

        if ($stockOriginal) {
            $stockOriginal->annulerLivraison($quantiteARestaurer);
        } else {
            // If original date stock doesn't exist, add to most recent stock
            $stockRecent = StockLait::where('id_cooperative', $cooperativeId)
                ->orderBy('date_stock', 'desc')
                ->first();

            if ($stockRecent) {
                $stockRecent->increment('quantite_disponible', $quantiteARestaurer);
                $stockRecent->decrement('quantite_livree', $quantiteARestaurer);
            } else {
                // Create new stock entry for today if no stock exists
                StockLait::create([
                    'id_cooperative' => $cooperativeId,
                    'date_stock' => $dateLivraison,
                    'quantite_totale' => $quantiteARestaurer,
                    'quantite_disponible' => $quantiteARestaurer,
                    'quantite_livree' => 0,
                ]);
            }
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

        // Count by status
        $statsQuery = clone $query;
        $planifiees = $statsQuery->where('statut', 'planifiee')->count();
        
        $statsQuery = clone $query;
        $validees = $statsQuery->where('statut', 'validee')->count();

        return [
            'total_livraisons' => $totalLivraisons,
            'total_quantite' => $totalQuantite,
            'livraisons_planifiees' => $planifiees,
            'livraisons_validees' => $validees,
        ];
    }
}