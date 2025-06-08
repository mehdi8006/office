<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\PaiementCooperativeUsine;
use App\Models\LivraisonUsine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaiementController extends Controller
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
     * Check if current user can access a paiement.
     */
    private function checkAccess($paiement)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        if ($paiement->id_cooperative != $cooperativeId) {
            abort(403, 'Vous ne pouvez pas accéder à ce paiement.');
        }
    }

    /**
     * Display a listing of paiements with filters and pagination.
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        $query = PaiementCooperativeUsine::with(['cooperative', 'livraison'])
            ->where('id_cooperative', $cooperativeId);

        // Filter by date range
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_paiement', [$request->date_debut, $request->date_fin]);
        } elseif ($request->filled('date_debut')) {
            $query->whereDate('date_paiement', '>=', $request->date_debut);
        } elseif ($request->filled('date_fin')) {
            $query->whereDate('date_paiement', '<=', $request->date_fin);
        } else {
            // Par défaut, afficher les 60 derniers jours
            $query->whereDate('date_paiement', '>=', now()->subDays(60));
        }

        // Filter by status
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'date_paiement');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $paiements = $query->paginate(15)->withQueryString();

        // Calculate statistics
        $stats = $this->calculatePaiementStats($cooperativeId, $request);

        // Get pending periods for auto-calculation
        $pendingPeriods = $this->getPendingPeriods($cooperativeId);

        return view('gestionnaire.paiements.index', compact('paiements', 'stats', 'cooperative', 'pendingPeriods'));
    }

    /**
     * Calculate payments for a specific period (every 15 days).
     */
    public function calculerPeriode(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        $validated = $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
        ], [
            'periode_debut.required' => 'La date de début est requise',
            'periode_fin.required' => 'La date de fin est requise',
            'periode_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
        ]);

        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($validated['periode_debut']);
            $endDate = Carbon::parse($validated['periode_fin']);

            // Get validated livraisons for this period
            $livraisons = LivraisonUsine::where('id_cooperative', $cooperativeId)
                ->where('statut', 'validee')
                ->whereBetween('date_livraison', [$startDate, $endDate])
                ->get();

            if ($livraisons->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'Aucune livraison validée trouvée pour cette période.');
            }

            $totalPaiements = 0;
            $nombrePaiements = 0;

            foreach ($livraisons as $livraison) {
                // Check if payment already exists
                $existingPaiement = PaiementCooperativeUsine::where('id_livraison', $livraison->id_livraison)->first();
                
                if (!$existingPaiement) {
                    // Create payment
                    $paiement = PaiementCooperativeUsine::create([
                        'id_cooperative' => $cooperativeId,
                        'id_livraison' => $livraison->id_livraison,
                        'date_paiement' => $endDate, // Payment date is end of period
                        'montant' => $livraison->montant_total,
                        'statut' => 'en_attente'
                    ]);
                    
                    $totalPaiements += $paiement->montant;
                    $nombrePaiements++;
                }
            }

            DB::commit();

            if ($nombrePaiements > 0) {
                return redirect()
                    ->route('gestionnaire.paiements.index')
                    ->with('success', sprintf(
                        'Paiements calculés avec succès ! %d paiement(s) créé(s) pour un montant total de %s DH',
                        $nombrePaiements,
                        number_format($totalPaiements, 2)
                    ));
            } else {
                return redirect()
                    ->back()
                    ->with('info', 'Tous les paiements pour cette période ont déjà été calculés.');
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', 'Erreur lors du calcul des paiements: ' . $e->getMessage());
        }
    }

    /**
     * Mark a payment as paid.
     */
    public function marquerPaye(PaiementCooperativeUsine $paiement)
    {
        $this->checkAccess($paiement);
        
        if ($paiement->statut !== 'en_attente') {
            return redirect()
                ->back()
                ->with('error', 'Ce paiement ne peut pas être marqué comme payé (statut: ' . $paiement->statut_label . ')');
        }

        try {
            $paiement->marquerPaye();
            
            return redirect()
                ->back()
                ->with('success', sprintf(
                    'Paiement marqué comme payé ! Montant: %s DH',
                    $paiement->montant_formattee
                ));
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics for the paiements listing.
     */
    private function calculatePaiementStats($cooperativeId, $request)
    {
        $query = PaiementCooperativeUsine::where('id_cooperative', $cooperativeId);

        // Apply same filters as main query
        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $query->whereBetween('date_paiement', [$request->date_debut, $request->date_fin]);
        } elseif ($request->filled('date_debut')) {
            $query->whereDate('date_paiement', '>=', $request->date_debut);
        } elseif ($request->filled('date_fin')) {
            $query->whereDate('date_paiement', '<=', $request->date_fin);
        } else {
            $query->whereDate('date_paiement', '>=', now()->subDays(60));
        }

        $totalPaiements = $query->count();
        $totalMontant = $query->sum('montant');

        // Count by status
        $statsQuery = clone $query;
        $enAttente = $statsQuery->where('statut', 'en_attente')->count();
        $montantEnAttente = $statsQuery->sum('montant');
        
        $statsQuery = clone $query;
        $payes = $statsQuery->where('statut', 'paye')->count();
        $montantPaye = $statsQuery->sum('montant');

        // Get pending livraisons (validated but not paid)
        $livraisonsValidees = LivraisonUsine::where('id_cooperative', $cooperativeId)
            ->where('statut', 'validee')
            ->whereDoesntHave('paiements')
            ->count();

        return [
            'total_paiements' => $totalPaiements,
            'total_montant' => $totalMontant,
            'paiements_en_attente' => $enAttente,
            'montant_en_attente' => $montantEnAttente,
            'paiements_payes' => $payes,
            'montant_paye' => $montantPaye,
            'livraisons_non_payees' => $livraisonsValidees,
        ];
    }

    /**
     * Get pending periods for automatic payment calculation.
     */
    private function getPendingPeriods($cooperativeId)
    {
        $periods = [];
        $currentDate = now();

        // Generate periods for current month and previous month
        for ($monthOffset = 0; $monthOffset <= 1; $monthOffset++) {
            $date = $currentDate->copy()->subMonths($monthOffset);
            
            // First period: 1st to 15th
            $period1Start = $date->copy()->startOfMonth();
            $period1End = $date->copy()->startOfMonth()->addDays(14);
            
            // Second period: 16th to end of month
            $period2Start = $date->copy()->startOfMonth()->addDays(15);
            $period2End = $date->copy()->endOfMonth();

            // Check if period has ended and has validated livraisons without payments
            if ($period1End->isPast()) {
                $hasUnpaidLivraisons = LivraisonUsine::where('id_cooperative', $cooperativeId)
                    ->where('statut', 'validee')
                    ->whereBetween('date_livraison', [$period1Start, $period1End])
                    ->whereDoesntHave('paiements')
                    ->exists();

                if ($hasUnpaidLivraisons) {
                    $periods[] = [
                        'label' => $period1Start->format('d/m/Y') . ' - ' . $period1End->format('d/m/Y'),
                        'debut' => $period1Start->format('Y-m-d'),
                        'fin' => $period1End->format('Y-m-d'),
                        'type' => 'Première quinzaine ' . $period1Start->format('M Y')
                    ];
                }
            }

            if ($period2End->isPast()) {
                $hasUnpaidLivraisons = LivraisonUsine::where('id_cooperative', $cooperativeId)
                    ->where('statut', 'validee')
                    ->whereBetween('date_livraison', [$period2Start, $period2End])
                    ->whereDoesntHave('paiements')
                    ->exists();

                if ($hasUnpaidLivraisons) {
                    $periods[] = [
                        'label' => $period2Start->format('d/m/Y') . ' - ' . $period2End->format('d/m/Y'),
                        'debut' => $period2Start->format('Y-m-d'),
                        'fin' => $period2End->format('Y-m-d'),
                        'type' => 'Deuxième quinzaine ' . $period2Start->format('M Y')
                    ];
                }
            }
        }

        return $periods;
    }
}