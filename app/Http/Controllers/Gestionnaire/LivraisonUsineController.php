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
     * Display a listing of PLANNED livraisons only (simplified version).
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        $query = LivraisonUsine::with('cooperative')
            ->where('id_cooperative', $cooperativeId)
            ->where('statut', 'planifiee'); // SEULEMENT LES PLANIFIÉES

        // Sort by date (most recent first by default)
        $sortBy = $request->get('sort_by', 'date_livraison');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $livraisons = $query->paginate(20)->withQueryString();

        return view('gestionnaire.livraisons.index', compact('livraisons', 'cooperative'));
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
     * Update the specified livraison (only for planned status).
     */
    public function update(Request $request, LivraisonUsine $livraison)
    {
        $this->checkAccess($livraison);
        
        // Only allow updating of planned livraisons
        if ($livraison->statut !== 'planifiee') {
            return redirect()
                ->back()
                ->with('error', 'Seules les livraisons planifiées peuvent être modifiées');
        }

        // Get current available stock for validation (including current livraison quantity)
        $stockTotalDisponible = StockLait::where('id_cooperative', $livraison->id_cooperative)
            ->sum('quantite_disponible') + $livraison->quantite_litres; // Add back current quantity
        
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

            $ancienneQuantite = $livraison->quantite_litres;
            $nouvelleQuantite = $validated['quantite_litres'];
            $difference = $nouvelleQuantite - $ancienneQuantite;

            // Update the livraison
            $livraison->update([
                'date_livraison' => Carbon::parse($validated['date_livraison'])->format('Y-m-d'),
                'quantite_litres' => $nouvelleQuantite,
            ]);

            // Adjust stock based on quantity difference
            if ($difference > 0) {
                // Need more stock - reduce available
                $this->reduceAvailableStock($livraison->id_cooperative, $difference);
            } elseif ($difference < 0) {
                // Need less stock - restore available using the correct method
                $this->restoreAvailableStockForUpdate($livraison->id_cooperative, abs($difference));
            }
            // If difference == 0, no stock adjustment needed

            DB::commit();

            return redirect()
                ->route('gestionnaire.livraisons.index')
                ->with('success', sprintf(
                    'Livraison modifiée avec succès ! Ancienne quantité: %s L - Nouvelle quantité: %s L (%s%s L)',
                    number_format($ancienneQuantite, 2),
                    number_format($nouvelleQuantite, 2),
                    $difference >= 0 ? '+' : '',
                    number_format($difference, 2)
                ));
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de la modification de livraison: " . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification de la livraison: ' . $e->getMessage());
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
     * Restore stock when a livraison is cancelled (CORRIGÉE).
     */
    private function restoreAvailableStock($cooperativeId, $quantiteARestaurer, $dateLivraison)
    {
        // Try to restore to the original date first
        $stockOriginal = StockLait::where('id_cooperative', $cooperativeId)
            ->whereDate('date_stock', $dateLivraison)
            ->first();

        if ($stockOriginal) {
            // Pour les livraisons planifiées, utiliser la nouvelle méthode sans vérifications
            $stockOriginal->annulerReservation($quantiteARestaurer);
        } else {
            // If original date stock doesn't exist, add to most recent stock
            $stockRecent = StockLait::where('id_cooperative', $cooperativeId)
                ->orderBy('date_stock', 'desc')
                ->first();

            if ($stockRecent) {
                $stockRecent->increment('quantite_disponible', $quantiteARestaurer);
                // Note: pas de decrement sur quantite_livree car le stock récent 
                // n'était pas concerné par cette livraison
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
     * Restore stock when updating a livraison (simpler version).
     */
    private function restoreAvailableStockForUpdate($cooperativeId, $quantiteARestaurer)
    {
        // Add back to the most recent stock with available space
        $stockRecent = StockLait::where('id_cooperative', $cooperativeId)
            ->orderBy('date_stock', 'desc')
            ->first();

        if ($stockRecent) {
            $stockRecent->annulerReservation($quantiteARestaurer);
        } else {
            // Create new stock entry for today if no stock exists
            StockLait::create([
                'id_cooperative' => $cooperativeId,
                'date_stock' => today(),
                'quantite_totale' => $quantiteARestaurer,
                'quantite_disponible' => $quantiteARestaurer,
                'quantite_livree' => 0,
            ]);
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
 * Download validated livraisons as PDF.
 */

/**
     * Download validated livraisons as PDF.
     */
    public function downloadLivraisonsValidees(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'inclure_details' => 'sometimes|boolean'
        ], [
            'date_debut.required' => 'La date de début est requise',
            'date_fin.required' => 'La date de fin est requise',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
        ]);

        try {
            $dateDebut = Carbon::parse($validated['date_debut']);
            $dateFin = Carbon::parse($validated['date_fin']);
            $inclureDetails = $request->has('inclure_details');

            // Get validated livraisons for the period
            $livraisons = LivraisonUsine::where('id_cooperative', $cooperativeId)
                ->where('statut', 'validee')
                ->whereBetween('date_livraison', [$dateDebut, $dateFin])
                ->orderBy('date_livraison', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($livraisons->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'Aucune livraison validée trouvée pour la période sélectionnée.');
            }

            // Calculate statistics (without montant)
            $stats = [
                'periode_debut' => $dateDebut,
                'periode_fin' => $dateFin,
                'total_livraisons' => $livraisons->count(),
                'total_quantite' => $livraisons->sum('quantite_litres'),
                'quantite_moyenne' => $livraisons->avg('quantite_litres') ?: 0,
                'premiere_livraison' => $livraisons->last()?->date_livraison,
                'derniere_livraison' => $livraisons->first()?->date_livraison,
                // SUPPRIMÉ: 'total_montant' => $livraisons->sum(...)
            ];

            // Group by date for summary if not including details (without montant)
            if (!$inclureDetails) {
                $livraisonsGroupees = $livraisons->groupBy(function($livraison) {
                    return $livraison->date_livraison->format('Y-m-d');
                })->map(function($group, $date) {
                    return [
                        'date' => Carbon::parse($date),
                        'nombre_livraisons' => $group->count(),
                        'quantite_totale' => $group->sum('quantite_litres'),
                        // SUPPRIMÉ: 'montant_total' => $group->sum(...)
                    ];
                });
            } else {
                $livraisonsGroupees = null;
            }

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('gestionnaire.livraisons.exports.livraisons-validees-pdf', compact(
                'cooperative', 
                'livraisons', 
                'stats', 
                'inclureDetails',
                'livraisonsGroupees'
            ));
            
            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');
            
            // Generate filename
            $filename = sprintf(
                'Livraisons_Validees_%s_%s_%s.pdf',
                str_replace(' ', '_', $cooperative->nom_cooperative),
                $dateDebut->format('Y-m-d'),
                $dateFin->format('Y-m-d')
            );
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la génération du PDF des livraisons: " . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }
}