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
     * Display quinzaines with their payment status and totals.
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        // Get selected month/year or default to current and previous month
        $selectedMonth = $request->get('mois', now()->month);
        $selectedYear = $request->get('annee', now()->year);
        
        // Calculate quinzaines data
        $quinzaines = $this->getQuinzainesData($cooperativeId, $selectedMonth, $selectedYear);
        
        // Calculate overall statistics
        $stats = $this->calculateQuinzainesStats($quinzaines);

        return view('gestionnaire.paiements.index', compact('quinzaines', 'stats', 'cooperative', 'selectedMonth', 'selectedYear'));
    }

    /**
     * Calculate quinzaines data for a specific month/year.
     */
    private function getQuinzainesData($cooperativeId, $month, $year)
    {
        $quinzaines = [];
        
        // Generate quinzaines for current and previous 2 months
        for ($monthOffset = 0; $monthOffset <= 2; $monthOffset++) {
            $date = Carbon::create($year, $month)->subMonths($monthOffset);
            
            // First quinzaine: 1st to 15th
            $quinzaine1 = $this->calculateQuinzaineData(
                $cooperativeId,
                $date->copy()->startOfMonth(),
                $date->copy()->startOfMonth()->addDays(14),
                "1-15 " . $date->translatedFormat('F Y')
            );
            
            // Second quinzaine: 16th to end of month
            $quinzaine2 = $this->calculateQuinzaineData(
                $cooperativeId,
                $date->copy()->startOfMonth()->addDays(15),
                $date->copy()->endOfMonth(),
                "16-" . $date->endOfMonth()->day . " " . $date->translatedFormat('F Y')
            );
            
            $quinzaines[] = $quinzaine1;
            $quinzaines[] = $quinzaine2;
        }
        
        // Sort by date desc (most recent first)
        usort($quinzaines, function($a, $b) {
            return $b['date_fin'] <=> $a['date_fin'];
        });
        
        return $quinzaines;
    }

    /**
     * Calculate data for a specific quinzaine period.
     */
    private function calculateQuinzaineData($cooperativeId, $dateDebut, $dateFin, $label)
    {
        // Get validated livraisons for this period
        $livraisons = LivraisonUsine::where('id_cooperative', $cooperativeId)
            ->where('statut', 'validee')
            ->whereBetween('date_livraison', [$dateDebut, $dateFin])
            ->get();

        // Calculate totals
        $totalQuantite = $livraisons->sum('quantite_litres');
        $totalMontant = $livraisons->sum('montant_total');
        $nombreLivraisons = $livraisons->count();

        // Check payment status
        $paiementsExistants = PaiementCooperativeUsine::where('id_cooperative', $cooperativeId)
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->get();

        $montantPaye = $paiementsExistants->where('statut', 'paye')->sum('montant');
        $montantEnAttente = $paiementsExistants->where('statut', 'en_attente')->sum('montant');
        $totalPaiements = $paiementsExistants->sum('montant');

        // Determine status
        $statut = 'non_calcule';
        $statutColor = 'secondary';
        $peutCalculer = false;

        if ($totalQuantite > 0) {
            if ($totalPaiements >= $totalMontant) {
                if ($montantPaye >= $totalMontant) {
                    $statut = 'paye';
                    $statutColor = 'success';
                } else {
                    $statut = 'calcule';
                    $statutColor = 'warning';
                }
            } else {
                $statut = 'non_calcule';
                $statutColor = 'danger';
                $peutCalculer = $dateFin->isPast(); // Only past periods can be calculated
            }
        }

        return [
            'periode_label' => $label,
            'date_debut' => $dateDebut->format('Y-m-d'),
            'date_fin' => $dateFin->format('Y-m-d'),
            'total_quantite' => $totalQuantite,
            'total_montant' => $totalMontant,
            'livraisons_count' => $nombreLivraisons,
            'montant_paye' => $montantPaye,
            'montant_en_attente' => $montantEnAttente,
            'statut' => $statut,
            'statut_color' => $statutColor,
            'peut_calculer' => $peutCalculer,
            'est_passe' => $dateFin->isPast(),
        ];
    }

    /**
     * Calculate overall statistics from quinzaines data.
     */
    private function calculateQuinzainesStats($quinzaines)
    {
        $stats = [
            'total_quinzaines' => count($quinzaines),
            'total_quantite' => 0,
            'total_montant' => 0,
            'total_livraisons' => 0,
            'montant_paye' => 0,
            'montant_en_attente' => 0,
            'quinzaines_payees' => 0,
            'quinzaines_calculees' => 0,
            'quinzaines_non_calculees' => 0,
        ];

        foreach ($quinzaines as $quinzaine) {
            $stats['total_quantite'] += $quinzaine['total_quantite'];
            $stats['total_montant'] += $quinzaine['total_montant'];
            $stats['total_livraisons'] += $quinzaine['livraisons_count'];
            $stats['montant_paye'] += $quinzaine['montant_paye'];
            $stats['montant_en_attente'] += $quinzaine['montant_en_attente'];

            switch ($quinzaine['statut']) {
                case 'paye':
                    $stats['quinzaines_payees']++;
                    break;
                case 'calcule':
                    $stats['quinzaines_calculees']++;
                    break;
                case 'non_calcule':
                    $stats['quinzaines_non_calculees']++;
                    break;
            }
        }

        return $stats;
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
}