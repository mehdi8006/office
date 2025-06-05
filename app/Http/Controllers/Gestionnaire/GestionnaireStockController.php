<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\StockLait;
use App\Models\LivraisonUsine;
use App\Models\Cooperative;
use App\Models\ReceptionLait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GestionnaireStockController extends Controller
{
    /**
     * Display stock overview for the gestionnaire's cooperative.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $date = $request->get('date', today());
        
        // Get or create today's stock
        $stockToday = StockLait::updateDailyStock($cooperative->id_cooperative, $date);
        
        // Get stock history (last 30 days)
        $stockHistory = StockLait::where('id_cooperative', $cooperative->id_cooperative)
                                ->whereBetween('date_stock', [now()->subDays(30), now()])
                                ->orderBy('date_stock', 'desc')
                                ->get();

        // Get weekly stats
        $weeklyStats = [
            'total_recu' => StockLait::where('id_cooperative', $cooperative->id_cooperative)
                                   ->whereBetween('date_stock', [now()->startOfWeek(), now()->endOfWeek()])
                                   ->sum('quantite_totale'),
            'total_livre' => StockLait::where('id_cooperative', $cooperative->id_cooperative)
                                    ->whereBetween('date_stock', [now()->startOfWeek(), now()->endOfWeek()])
                                    ->sum('quantite_livree'),
            'total_disponible' => StockLait::where('id_cooperative', $cooperative->id_cooperative)
                                         ->whereBetween('date_stock', [now()->startOfWeek(), now()->endOfWeek()])
                                         ->sum('quantite_disponible'),
        ];

        // Get monthly stats
        $monthlyStats = [
            'total_recu' => StockLait::where('id_cooperative', $cooperative->id_cooperative)
                                   ->whereMonth('date_stock', now()->month)
                                   ->whereYear('date_stock', now()->year)
                                   ->sum('quantite_totale'),
            'total_livre' => StockLait::where('id_cooperative', $cooperative->id_cooperative)
                                    ->whereMonth('date_stock', now()->month)
                                    ->whereYear('date_stock', now()->year)
                                    ->sum('quantite_livree'),
            'moyenne_journaliere' => StockLait::where('id_cooperative', $cooperative->id_cooperative)
                                             ->whereMonth('date_stock', now()->month)
                                             ->whereYear('date_stock', now()->year)
                                             ->avg('quantite_totale'),
        ];

        // Get recent livraisons
        $recentLivraisons = LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)
                                         ->with(['cooperative'])
                                         ->latest('date_livraison')
                                         ->take(5)
                                         ->get();

        return view('gestionnaire.stock.index', compact(
            'stockToday', 
            'stockHistory', 
            'cooperative', 
            'weeklyStats', 
            'monthlyStats', 
            'recentLivraisons',
            'date'
        ));
    }

    /**
     * Show stock details for a specific date.
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $date = $request->get('date', today());
        
        // Get or create stock for this date
        $stock = StockLait::updateDailyStock($cooperative->id_cooperative, $date);
        
        // Get all receptions for this date
        $receptions = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                                  ->whereDate('date_reception', $date)
                                  ->with(['membre'])
                                  ->latest('created_at')
                                  ->get();

        // Get livraisons for this date
        $livraisons = LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)
                                   ->whereDate('date_livraison', $date)
                                   ->get();

        // Reception summary
        $receptionSummary = [
            'total_receptions' => $receptions->count(),
            'total_litres' => $receptions->sum('quantite_litres'),
            'nombre_eleveurs' => $receptions->pluck('id_membre')->unique()->count(),
            'moyenne_par_eleveur' => $receptions->count() > 0 ? $receptions->sum('quantite_litres') / $receptions->pluck('id_membre')->unique()->count() : 0,
        ];

        return view('gestionnaire.stock.show', compact(
            'stock', 
            'receptions', 
            'livraisons',
            'receptionSummary',
            'cooperative',
            'date'
        ));
    }

    /**
     * Show form to create a new livraison.
     */
    public function createLivraison(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $date = $request->get('date', today());
        
        // Get stock for this date
        $stock = StockLait::where('id_cooperative', $cooperative->id_cooperative)
                         ->where('date_stock', $date)
                         ->first();

        if (!$stock || $stock->quantite_disponible <= 0) {
            return redirect()->route('gestionnaire.stock.index')
                           ->with('error', 'لا يوجد مخزون متاح للتسليم في هذا التاريخ');
        }

        return view('gestionnaire.stock.create-livraison', compact('stock', 'cooperative', 'date'));
    }

    /**
     * Store a new livraison.
     */
    public function storeLivraison(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $request->validate([
            'date_livraison' => 'required|date',
            'quantite_litres' => 'required|numeric|min:0.01',
            'prix_unitaire' => 'required|numeric|min:0.01',
        ], [
            'date_livraison.required' => 'تاريخ التسليم مطلوب',
            'date_livraison.date' => 'تاريخ التسليم غير صحيح',
            'quantite_litres.required' => 'كمية اللتر مطلوبة',
            'quantite_litres.numeric' => 'كمية اللتر يجب أن تكون رقم',
            'quantite_litres.min' => 'كمية اللتر يجب أن تكون أكبر من 0',
            'prix_unitaire.required' => 'السعر لكل لتر مطلوب',
            'prix_unitaire.numeric' => 'السعر لكل لتر يجب أن يكون رقم',
            'prix_unitaire.min' => 'السعر لكل لتر يجب أن يكون أكبر من 0',
        ]);

        // Get stock for the specified date
        $stock = StockLait::where('id_cooperative', $cooperative->id_cooperative)
                         ->where('date_stock', $request->date_livraison)
                         ->first();

        if (!$stock) {
            return redirect()->back()
                           ->with('error', 'لا يوجد مخزون في هذا التاريخ');
        }

        if ($request->quantite_litres > $stock->quantite_disponible) {
            return redirect()->back()
                           ->with('error', 'الكمية المطلوبة تتجاوز المخزون المتاح (' . $stock->quantite_disponible . ' لتر)')
                           ->withInput();
        }

        DB::transaction(function () use ($request, $cooperative, $stock) {
            // Create livraison
            $livraison = LivraisonUsine::create([
                'id_cooperative' => $cooperative->id_cooperative,
                'date_livraison' => $request->date_livraison,
                'quantite_litres' => $request->quantite_litres,
                'prix_unitaire' => $request->prix_unitaire,
                'statut' => 'planifiee',
            ]);

            // Update stock
            $stock->livrer($request->quantite_litres);
        });

        return redirect()->route('gestionnaire.stock.show', ['date' => $request->date_livraison])
                        ->with('success', 'تم إنشاء التسليم بنجاح');
    }

    /**
     * Show livraisons management.
     */
    public function livraisons(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $query = LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative);

        // Apply filters
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_livraison', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_livraison', '<=', $request->date_to);
        }

        $livraisons = $query->latest('date_livraison')
                           ->latest('created_at')
                           ->paginate(15);

        // Get statistics
        $stats = [
            'total_livraisons' => LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)->count(),
            'total_litres' => LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)->sum('quantite_litres'),
            'total_montant' => LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)->sum('montant_total'),
            'planifiees' => LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)->planifiee()->count(),
            'validees' => LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)->validee()->count(),
            'payees' => LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)->payee()->count(),
        ];

        return view('gestionnaire.stock.livraisons', compact('livraisons', 'cooperative', 'stats'));
    }

    /**
     * Update livraison status.
     */
    public function updateLivraisonStatus(Request $request, $id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $livraison = LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)
                                  ->where('id_livraison', $id)
                                  ->firstOrFail();

        $request->validate([
            'statut' => 'required|in:planifiee,validee,payee',
        ]);

        // Only allow certain transitions
        $allowedTransitions = [
            'planifiee' => ['validee'],
            'validee' => ['payee'],
            'payee' => [], // Cannot change from payee
        ];

        if (!in_array($request->statut, $allowedTransitions[$livraison->statut])) {
            return response()->json(['error' => 'تغيير الحالة غير مسموح'], 400);
        }

        if ($request->statut === 'validee') {
            $livraison->valider();
            $message = 'تم تأكيد التسليم بنجاح';
        } elseif ($request->statut === 'payee') {
            $livraison->marquerPayee();
            $message = 'تم تحديد التسليم كمدفوع بنجاح';
        }

        return response()->json(['success' => $message]);
    }

    /**
     * Cancel a livraison (only if planifiee).
     */
    public function cancelLivraison($id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $livraison = LivraisonUsine::where('id_cooperative', $cooperative->id_cooperative)
                                  ->where('id_livraison', $id)
                                  ->firstOrFail();

        if ($livraison->statut !== 'planifiee') {
            return response()->json(['error' => 'لا يمكن إلغاء تسليم غير مخطط له'], 400);
        }

        DB::transaction(function () use ($livraison, $cooperative) {
            // Return quantity to stock
            $stock = StockLait::where('id_cooperative', $cooperative->id_cooperative)
                             ->where('date_stock', $livraison->date_livraison)
                             ->first();
            
            if ($stock) {
                $stock->annulerLivraison($livraison->quantite_litres);
            }
            
            // Delete livraison
            $livraison->delete();
        });

        return response()->json(['success' => 'تم إلغاء التسليم بنجاح']);
    }

    /**
     * Get stock data for charts.
     */
    public function getStockData(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $days = $request->get('days', 7);
        $endDate = now();
        $startDate = now()->subDays($days);

        $stockData = StockLait::where('id_cooperative', $cooperative->id_cooperative)
                             ->whereBetween('date_stock', [$startDate, $endDate])
                             ->orderBy('date_stock')
                             ->get();

        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Reçu',
                    'data' => [],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Disponible',
                    'data' => [],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Livré',
                    'data' => [],
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 2,
                ],
            ],
        ];

        foreach ($stockData as $stock) {
            $chartData['labels'][] = $stock->date_stock->format('d/m');
            $chartData['datasets'][0]['data'][] = (float) $stock->quantite_totale;
            $chartData['datasets'][1]['data'][] = (float) $stock->quantite_disponible;
            $chartData['datasets'][2]['data'][] = (float) $stock->quantite_livree;
        }

        return response()->json($chartData);
    }

    /**
     * Export stock data.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $query = StockLait::where('id_cooperative', $cooperative->id_cooperative);

        if ($request->filled('date_from')) {
            $query->whereDate('date_stock', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_stock', '<=', $request->date_to);
        }

        $stocks = $query->orderBy('date_stock', 'desc')->get();

        $filename = 'stock_' . $cooperative->nom_cooperative . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($stocks) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'التاريخ',
                'المخزون الإجمالي (لتر)',
                'المخزون المتاح (لتر)',
                'المخزون المُسلم (لتر)',
                'نسبة التسليم (%)',
            ]);

            // Data
            foreach ($stocks as $stock) {
                fputcsv($file, [
                    $stock->date_stock->format('Y-m-d'),
                    $stock->quantite_totale,
                    $stock->quantite_disponible,
                    $stock->quantite_livree,
                    $stock->percentage_livre,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}