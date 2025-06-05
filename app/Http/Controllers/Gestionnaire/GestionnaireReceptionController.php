<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\ReceptionLait;
use App\Models\MembreEleveur;
use App\Models\Cooperative;
use App\Models\StockLait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GestionnaireReceptionController extends Controller
{
    /**
     * Display a listing of receptions for the gestionnaire's cooperative.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $query = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                             ->with(['membre', 'cooperative']);

        // Apply filters
        if ($request->filled('membre_id')) {
            $query->where('id_membre', $request->membre_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_reception', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_reception', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('matricule_reception', 'like', "%{$search}%")
                  ->orWhereHas('membre', function($membreQuery) use ($search) {
                      $membreQuery->where('nom_complet', 'like', "%{$search}%");
                  });
            });
        }

        $receptions = $query->latest('date_reception')
                           ->latest('created_at')
                           ->paginate(20);

        // Get statistics
        $stats = [
            'total_receptions' => ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)->count(),
            'total_litres' => ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)->sum('quantite_litres'),
            'receptions_aujourdhui' => ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                                                  ->whereDate('date_reception', today())
                                                  ->count(),
            'litres_aujourdhui' => ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                                               ->whereDate('date_reception', today())
                                               ->sum('quantite_litres'),
            'moyenne_journaliere' => ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                                                 ->whereMonth('date_reception', now()->month)
                                                 ->avg('quantite_litres'),
        ];

        // Get membres for filter dropdown
        $membres = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)
                               ->actif()
                               ->orderBy('nom_complet')
                               ->get();

        return view('gestionnaire.receptions.index', compact('receptions', 'cooperative', 'stats', 'membres'));
    }

    /**
     * Show the form for creating a new reception.
     */
    public function create()
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        // Get active membres
        $membres = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)
                               ->actif()
                               ->orderBy('nom_complet')
                               ->get();

        return view('gestionnaire.receptions.create', compact('cooperative', 'membres'));
    }

    /**
     * Store a newly created reception in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $request->validate([
            'id_membre' => 'required|exists:membres_eleveurs,id_membre',
            'date_reception' => 'required|date|before_or_equal:today',
            'quantite_litres' => 'required|numeric|min:0.01|max:9999.99',
        ], [
            'id_membre.required' => 'يجب اختيار المربي',
            'id_membre.exists' => 'المربي المختار غير موجود',
            'date_reception.required' => 'تاريخ الاستلام مطلوب',
            'date_reception.date' => 'تاريخ الاستلام غير صحيح',
            'date_reception.before_or_equal' => 'لا يمكن أن يكون تاريخ الاستلام في المستقبل',
            'quantite_litres.required' => 'كمية اللتر مطلوبة',
            'quantite_litres.numeric' => 'كمية اللتر يجب أن تكون رقم',
            'quantite_litres.min' => 'كمية اللتر يجب أن تكون أكبر من 0',
            'quantite_litres.max' => 'كمية اللتر لا يمكن أن تتجاوز 9999.99',
        ]);

        // Verify membre belongs to this cooperative
        $membre = MembreEleveur::where('id_membre', $request->id_membre)
                              ->where('id_cooperative', $cooperative->id_cooperative)
                              ->actif()
                              ->firstOrFail();

        DB::transaction(function () use ($request, $cooperative, $membre) {
            // Create reception
            $reception = ReceptionLait::create([
                'id_cooperative' => $cooperative->id_cooperative,
                'id_membre' => $membre->id_membre,
                'date_reception' => $request->date_reception,
                'quantite_litres' => $request->quantite_litres,
            ]);

            // Update daily stock
            StockLait::updateDailyStock($cooperative->id_cooperative, $request->date_reception);
        });

        return redirect()->route('gestionnaire.receptions.index')
                        ->with('success', 'تم تسجيل الاستلام بنجاح');
    }

    /**
     * Display the specified reception.
     */
    public function show($id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $reception = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                                 ->where('id_reception', $id)
                                 ->with(['membre', 'cooperative'])
                                 ->firstOrFail();

        return view('gestionnaire.receptions.show', compact('reception'));
    }

    /**
     * Show the form for editing the specified reception.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $reception = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                                 ->where('id_reception', $id)
                                 ->with(['membre'])
                                 ->firstOrFail();

        // Only allow editing recent receptions (within 7 days)
        if ($reception->created_at < now()->subDays(7)) {
            return redirect()->route('gestionnaire.receptions.show', $reception->id_reception)
                           ->with('error', 'لا يمكن تعديل الاستلامات القديمة');
        }

        // Get active membres
        $membres = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)
                               ->actif()
                               ->orderBy('nom_complet')
                               ->get();

        return view('gestionnaire.receptions.edit', compact('reception', 'membres', 'cooperative'));
    }

    /**
     * Update the specified reception in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $reception = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                                 ->where('id_reception', $id)
                                 ->firstOrFail();

        // Only allow editing recent receptions
        if ($reception->created_at < now()->subDays(7)) {
            return redirect()->route('gestionnaire.receptions.show', $reception->id_reception)
                           ->with('error', 'لا يمكن تعديل الاستلامات القديمة');
        }

        $request->validate([
            'id_membre' => 'required|exists:membres_eleveurs,id_membre',
            'date_reception' => 'required|date|before_or_equal:today',
            'quantite_litres' => 'required|numeric|min:0.01|max:9999.99',
        ], [
            'id_membre.required' => 'يجب اختيار المربي',
            'id_membre.exists' => 'المربي المختار غير موجود',
            'date_reception.required' => 'تاريخ الاستلام مطلوب',
            'date_reception.date' => 'تاريخ الاستلام غير صحيح',
            'date_reception.before_or_equal' => 'لا يمكن أن يكون تاريخ الاستلام في المستقبل',
            'quantite_litres.required' => 'كمية اللتر مطلوبة',
            'quantite_litres.numeric' => 'كمية اللتر يجب أن تكون رقم',
            'quantite_litres.min' => 'كمية اللتر يجب أن تكون أكبر من 0',
            'quantite_litres.max' => 'كمية اللتر لا يمكن أن تتجاوز 9999.99',
        ]);

        // Verify membre belongs to this cooperative
        $membre = MembreEleveur::where('id_membre', $request->id_membre)
                              ->where('id_cooperative', $cooperative->id_cooperative)
                              ->actif()
                              ->firstOrFail();

        DB::transaction(function () use ($request, $reception, $cooperative) {
            $oldDate = $reception->date_reception;
            
            // Update reception
            $reception->update([
                'id_membre' => $request->id_membre,
                'date_reception' => $request->date_reception,
                'quantite_litres' => $request->quantite_litres,
            ]);

            // Update stock for both old and new dates
            StockLait::updateDailyStock($cooperative->id_cooperative, $oldDate);
            if ($oldDate != $request->date_reception) {
                StockLait::updateDailyStock($cooperative->id_cooperative, $request->date_reception);
            }
        });

        return redirect()->route('gestionnaire.receptions.show', $reception->id_reception)
                        ->with('success', 'تم تحديث الاستلام بنجاح');
    }

    /**
     * Remove the specified reception from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $reception = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                                 ->where('id_reception', $id)
                                 ->firstOrFail();

        // Only allow deleting recent receptions
        if ($reception->created_at < now()->subDays(7)) {
            return response()->json(['error' => 'لا يمكن حذف الاستلامات القديمة'], 403);
        }

        DB::transaction(function () use ($reception, $cooperative) {
            $date = $reception->date_reception;
            $reception->delete();
            
            // Update stock after deletion
            StockLait::updateDailyStock($cooperative->id_cooperative, $date);
        });

        return response()->json(['success' => 'تم حذف الاستلام بنجاح']);
    }

    /**
     * Get daily summary of receptions.
     */
    public function dailySummary(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $date = $request->get('date', today());
        
        $summary = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                               ->whereDate('date_reception', $date)
                               ->selectRaw('
                                   COUNT(*) as total_receptions,
                                   SUM(quantite_litres) as total_litres,
                                   AVG(quantite_litres) as moyenne_litres,
                                   MIN(quantite_litres) as min_litres,
                                   MAX(quantite_litres) as max_litres
                               ')
                               ->first();

        $receptions = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                                  ->whereDate('date_reception', $date)
                                  ->with(['membre'])
                                  ->latest('created_at')
                                  ->get();

        return response()->json([
            'summary' => $summary,
            'receptions' => $receptions,
            'date' => Carbon::parse($date)->format('Y-m-d')
        ]);
    }

    /**
     * Export receptions to CSV.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return redirect()->route('gestionnaire.dashboard')
                           ->with('error', 'Aucune coopérative assignée à votre compte.');
        }

        $query = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
                             ->with(['membre']);

        // Apply same filters as index
        if ($request->filled('membre_id')) {
            $query->where('id_membre', $request->membre_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_reception', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_reception', '<=', $request->date_to);
        }

        $receptions = $query->latest('date_reception')->get();

        $filename = 'receptions_' . $cooperative->nom_cooperative . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($receptions) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'رقم الاستلام',
                'اسم المربي',
                'تاريخ الاستلام',
                'الكمية (لتر)',
                'تاريخ التسجيل'
            ]);

            // Data
            foreach ($receptions as $reception) {
                fputcsv($file, [
                    $reception->matricule_reception,
                    $reception->membre->nom_complet,
                    $reception->date_reception->format('Y-m-d'),
                    $reception->quantite_litres,
                    $reception->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get membre autocomplete data.
     */
    public function getMembreAutocomplete(Request $request)
    {
        $user = Auth::user();
        $cooperative = $user->cooperatives()->first();
        
        if (!$cooperative) {
            return response()->json([]);
        }

        $search = $request->get('q', '');
        
        $membres = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)
                               ->actif()
                               ->where('nom_complet', 'like', "%{$search}%")
                               ->select('id_membre', 'nom_complet', 'numero_carte_nationale')
                               ->limit(10)
                               ->get();

        return response()->json($membres->map(function ($membre) {
            return [
                'id' => $membre->id_membre,
                'text' => $membre->nom_complet . ' (' . $membre->numero_carte_nationale . ')',
                'nom' => $membre->nom_complet,
                'carte' => $membre->numero_carte_nationale,
            ];
        }));
    }
}