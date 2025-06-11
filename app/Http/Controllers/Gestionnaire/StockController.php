<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\StockLait;
use App\Models\ReceptionLait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Display daily receptions with simplified view.
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        // Build query for receptions grouped by date
        $query = ReceptionLait::select([
                'date_reception',
                DB::raw('COUNT(*) as nombre_receptions'),
                DB::raw('SUM(quantite_litres) as quantite_totale')
            ])
            ->where('id_cooperative', $cooperativeId)
            ->groupBy('date_reception');

        // Filter by date range
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_reception', [$request->date_debut, $request->date_fin]);
        } elseif ($request->filled('date_debut')) {
            $query->whereDate('date_reception', '>=', $request->date_debut);
        } elseif ($request->filled('date_fin')) {
            $query->whereDate('date_reception', '<=', $request->date_fin);
        } else {
            // Par défaut, afficher les 30 derniers jours
            $query->whereDate('date_reception', '>=', now()->subDays(30));
        }

        // Filter to show only days with receptions
        if ($request->has('avec_receptions_seulement')) {
            // This is already implicit since we're grouping by date_reception
        }

        // Sort by date (most recent first)
        $sortBy = $request->get('sort_by', 'date_reception');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Get results
        $receptions = $query->paginate(20)->withQueryString();

        // Calculate total available stock (non livré) for the entire cooperative
        $stockTotalRestant = $this->calculateStockTotalRestant($cooperativeId);

        return view('gestionnaire.stock.index', compact(
            'receptions', 
            'cooperative', 
            'stockTotalRestant'
        ));
    }

    /**
     * Calculate total available stock (non delivered) for the cooperative.
     */
    private function calculateStockTotalRestant($cooperativeId)
    {
        // Get sum of all available quantities from all stock days
        $stockTotal = StockLait::where('id_cooperative', $cooperativeId)
            ->sum('quantite_disponible');

        return $stockTotal ?? 0;
    }

    /**
     * Display the specified date's details (optional - can redirect to receptions).
     */
    public function show(Request $request, $date)
    {
        // Redirect to receptions page for this date
        return redirect()
            ->route('gestionnaire.receptions.index', ['date' => $date])
            ->with('info', 'Consultez les réceptions pour voir les détails du ' . Carbon::parse($date)->format('d/m/Y'));
    }
}