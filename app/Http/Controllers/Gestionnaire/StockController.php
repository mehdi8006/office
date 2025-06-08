<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\StockLait;
use App\Models\LivraisonUsine;
use App\Models\ReceptionLait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

class StockController extends Controller
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
     * Display a listing of stock with filters and pagination.
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        $query = StockLait::with('cooperative')
            ->where('id_cooperative', $cooperativeId);

        // Filter by date range
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_stock', [$request->date_debut, $request->date_fin]);
        } elseif ($request->filled('date_debut')) {
            $query->whereDate('date_stock', '>=', $request->date_debut);
        } elseif ($request->filled('date_fin')) {
            $query->whereDate('date_stock', '<=', $request->date_fin);
        } else {
            // Par défaut, afficher les 30 derniers jours
            $query->whereDate('date_stock', '>=', now()->subDays(30));
        }

        // Filter by stock status
        if ($request->filled('statut_stock')) {
            switch ($request->statut_stock) {
                case 'non_livre':
                    $query->where('quantite_livree', 0)->where('quantite_totale', '>', 0);
                    break;
                case 'partiellement_livre':
                    $query->where('quantite_livree', '>', 0)->whereRaw('quantite_livree < quantite_totale');
                    break;
                case 'entierement_livre':
                    $query->where('quantite_livree', '>', 0)->whereRaw('quantite_livree = quantite_totale');
                    break;
                case 'stock_vide':
                    $query->where('quantite_totale', 0);
                    break;
            }
        }

        // Filter by available stock only
        if ($request->has('avec_stock_seulement')) {
            $query->where('quantite_disponible', '>', 0);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date_stock');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $stocks = $query->paginate(15)->withQueryString();

        // Calculate statistics
        $stats = $this->calculateStockStats($cooperativeId, $request);

        return view('gestionnaire.stock.index', compact('stocks', 'stats', 'cooperative'));
    }

    /**
     * Display the specified stock with detailed information.
     */
    /**
     * Display the specified stock with detailed information.
     */
    public function show(Request $request, $date)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        // Validate date format
        try {
            $stockDate = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            return redirect()
                ->route('gestionnaire.stock.index')
                ->with('error', 'Format de date invalide');
        }

        try {
            // Utiliser la méthode sécurisée pour obtenir le stock
            $stock = StockLait::getOrCreateDailyStock($cooperativeId, $stockDate);

            // Get receptions for this date
            $receptions = ReceptionLait::where('id_cooperative', $cooperativeId)
                ->whereDate('date_reception', $stockDate)
                ->with('membre')
                ->latest('created_at')
                ->get();

            // Get livraisons for this stock
            $livraisons = LivraisonUsine::where('id_cooperative', $cooperativeId)
                ->whereDate('date_livraison', $stockDate)
                ->latest('created_at')
                ->get();

            return view('gestionnaire.stock.show', compact('stock', 'receptions', 'livraisons', 'cooperative'));

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la récupération du stock: " . $e->getMessage());
            
            return redirect()
                ->route('gestionnaire.stock.index')
                ->with('error', 'Erreur lors de la récupération du stock pour cette date.');
        }
    }

    /**
     * Calculate statistics for the stock listing.
     */
    private function calculateStockStats($cooperativeId, $request)
    {
        $query = StockLait::where('id_cooperative', $cooperativeId);

        // Apply same filters as main query
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_stock', [$request->date_debut, $request->date_fin]);
        } elseif ($request->filled('date_debut')) {
            $query->whereDate('date_stock', '>=', $request->date_debut);
        } elseif ($request->filled('date_fin')) {
            $query->whereDate('date_stock', '<=', $request->date_fin);
        } else {
            $query->whereDate('date_stock', '>=', now()->subDays(30));
        }

        $totalStocks = $query->count();
        $totalQuantite = $query->sum('quantite_totale');
        $totalDisponible = $query->sum('quantite_disponible');
        $totalLivree = $query->sum('quantite_livree');

        // Count by status
        $statsQuery = clone $query;
        $stocksAvecQuantite = $statsQuery->where('quantite_totale', '>', 0)->count();
        
        $statsQuery = clone $query;
        $stocksNonLivres = $statsQuery->where('quantite_livree', 0)->where('quantite_totale', '>', 0)->count();
        
        $statsQuery = clone $query;
        $stocksEntierementLivres = $statsQuery->where('quantite_livree', '>', 0)
            ->whereRaw('quantite_livree = quantite_totale')->count();

        $statsQuery = clone $query;
        $stocksPartiellementLivres = $statsQuery->where('quantite_livree', '>', 0)
            ->whereRaw('quantite_livree < quantite_totale')->count();

        return [
            'total_jours' => $totalStocks,
            'jours_avec_stock' => $stocksAvecQuantite,
            'total_quantite' => $totalQuantite,
            'total_disponible' => $totalDisponible,
            'total_livree' => $totalLivree,
            'stocks_non_livres' => $stocksNonLivres,
            'stocks_partiellement_livres' => $stocksPartiellementLivres,
            'stocks_entierement_livres' => $stocksEntierementLivres,
            'taux_livraison' => $totalQuantite > 0 ? ($totalLivree / $totalQuantite) * 100 : 0,
        ];
    }
}