<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\PaiementCooperativeEleveur;
use App\Models\MembreEleveur;
use App\Models\ReceptionLait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaiementEleveurController extends Controller
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
     * Display membres with their payments for selected quinzaine.
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        // Get selected month/year or default to current
        $selectedMonth = $request->get('mois', now()->month);
        $selectedYear = $request->get('annee', now()->year);
        $selectedQuinzaine = $request->get('quinzaine', $this->getCurrentQuinzaine());
        
        // Calculate quinzaine dates
        $dates = $this->getQuinzaineDates($selectedMonth, $selectedYear, $selectedQuinzaine);
        
        // Get membres with their reception data for this quinzaine
        $membresData = $this->getMembresDataForQuinzaine($cooperativeId, $dates['debut'], $dates['fin']);
        
        // Calculate statistics
        $stats = $this->calculateStats($membresData);
        
        // Check if quinzaine can be calculated
        $peutCalculer = $dates['fin']->isPast() && $stats['non_calcules'] > 0;
        
        return view('gestionnaire.paiements-eleveurs.index', compact(
            'membresData', 
            'stats', 
            'cooperative', 
            'selectedMonth', 
            'selectedYear', 
            'selectedQuinzaine',
            'dates',
            'peutCalculer'
        ));
    }

    /**
     * Get current quinzaine (1 or 2).
     */
    private function getCurrentQuinzaine()
    {
        $day = now()->day;
        return $day <= 15 ? 1 : 2;
    }

    /**
     * Get quinzaine start and end dates.
     */
    private function getQuinzaineDates($month, $year, $quinzaine)
    {
        $date = Carbon::create($year, $month);
        
        if ($quinzaine == 1) {
            return [
                'debut' => $date->copy()->startOfMonth(),
                'fin' => $date->copy()->startOfMonth()->addDays(14),
                'label' => "1-15 " . $date->translatedFormat('F Y')
            ];
        } else {
            return [
                'debut' => $date->copy()->startOfMonth()->addDays(15),
                'fin' => $date->copy()->endOfMonth(),
                'label' => "16-" . $date->endOfMonth()->day . " " . $date->translatedFormat('F Y')
            ];
        }
    }

    /**
     * Get membres data with their receptions for the quinzaine.
     */
    private function getMembresDataForQuinzaine($cooperativeId, $dateDebut, $dateFin)
    {
        // Get all active members
        $membres = MembreEleveur::where('id_cooperative', $cooperativeId)
            ->actif()
            ->orderBy('nom_complet')
            ->get();

        $membresData = [];

        foreach ($membres as $membre) {
            // Get receptions for this quinzaine
            $receptions = ReceptionLait::where('id_membre', $membre->id_membre)
                ->whereBetween('date_reception', [$dateDebut, $dateFin])
                ->get();

            $quantiteTotale = $receptions->sum('quantite_litres');

            // Check existing payment for this period
            $paiementExistant = PaiementCooperativeEleveur::where('id_membre', $membre->id_membre)
                ->where('periode_debut', $dateDebut->format('Y-m-d'))
                ->where('periode_fin', $dateFin->format('Y-m-d'))
                ->first();

            $statut = 'non_calcule';
            $statutColor = 'secondary';
            $montantCalcule = 0;

            if ($paiementExistant) {
                $montantCalcule = $paiementExistant->montant_total;
                if ($paiementExistant->statut === 'paye') {
                    $statut = 'paye';
                    $statutColor = 'success';
                } else {
                    $statut = 'en_attente';
                    $statutColor = 'warning';
                }
            } elseif ($quantiteTotale > 0) {
                // Calculate potential amount with default price
                $montantCalcule = $quantiteTotale * 2.50; // Default price, can be configurable
                $statut = 'non_calcule';
                $statutColor = 'danger';
            }

            // Only include members who have delivered milk or have existing payments
            if ($quantiteTotale > 0 || $paiementExistant) {
                $membresData[] = [
                    'membre' => $membre,
                    'quantite_totale' => $quantiteTotale,
                    'montant_calcule' => $montantCalcule,
                    'statut' => $statut,
                    'statut_color' => $statutColor,
                    'paiement' => $paiementExistant,
                    'receptions_count' => $receptions->count(),
                ];
            }
        }

        return $membresData;
    }

    /**
     * Calculate statistics from membres data.
     */
    private function calculateStats($membresData)
    {
        $stats = [
            'total_membres' => count($membresData),
            'total_quantite' => 0,
            'total_montant' => 0,
            'montant_paye' => 0,
            'montant_en_attente' => 0,
            'non_calcules' => 0,
            'en_attente' => 0,
            'payes' => 0,
        ];

        foreach ($membresData as $data) {
            $stats['total_quantite'] += $data['quantite_totale'];
            $stats['total_montant'] += $data['montant_calcule'];

            switch ($data['statut']) {
                case 'non_calcule':
                    $stats['non_calcules']++;
                    break;
                case 'en_attente':
                    $stats['en_attente']++;
                    $stats['montant_en_attente'] += $data['montant_calcule'];
                    break;
                case 'paye':
                    $stats['payes']++;
                    $stats['montant_paye'] += $data['montant_calcule'];
                    break;
            }
        }

        return $stats;
    }

    /**
     * Calculate payments for all members in the quinzaine.
     */
    public function calculerQuinzaine(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        $validated = $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'prix_unitaire' => 'required|numeric|min:0.1|max:999.99',
        ], [
            'periode_debut.required' => 'La date de début est requise',
            'periode_fin.required' => 'La date de fin est requise',
            'prix_unitaire.required' => 'Le prix unitaire est requis',
            'prix_unitaire.numeric' => 'Le prix unitaire doit être un nombre',
        ]);

        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($validated['periode_debut']);
            $endDate = Carbon::parse($validated['periode_fin']);
            $prixUnitaire = $validated['prix_unitaire'];

            // Get all active members
            $membres = MembreEleveur::where('id_cooperative', $cooperativeId)
                ->actif()
                ->get();

            $paiementsCreated = 0;
            $montantTotal = 0;

            foreach ($membres as $membre) {
                // Check if payment already exists
                $existingPaiement = PaiementCooperativeEleveur::where('id_membre', $membre->id_membre)
                    ->where('periode_debut', $startDate->format('Y-m-d'))
                    ->where('periode_fin', $endDate->format('Y-m-d'))
                    ->first();

                if ($existingPaiement) {
                    continue; // Skip if already calculated
                }

                // Calculate member's total quantity for this period
                $quantiteTotale = ReceptionLait::where('id_membre', $membre->id_membre)
                    ->whereBetween('date_reception', [$startDate, $endDate])
                    ->sum('quantite_litres');

                if ($quantiteTotale > 0) {
                    $montant = $quantiteTotale * $prixUnitaire;

                    PaiementCooperativeEleveur::create([
                        'id_membre' => $membre->id_membre,
                        'id_cooperative' => $cooperativeId,
                        'periode_debut' => $startDate->format('Y-m-d'),
                        'periode_fin' => $endDate->format('Y-m-d'),
                        'quantite_totale' => $quantiteTotale,
                        'prix_unitaire' => $prixUnitaire,
                        'montant_total' => $montant,
                        'statut' => 'calcule'
                    ]);

                    $paiementsCreated++;
                    $montantTotal += $montant;
                }
            }

            DB::commit();

            if ($paiementsCreated > 0) {
                return redirect()
                    ->back()
                    ->with('success', sprintf(
                        'Paiements calculés avec succès ! %d membre(s) - Montant total: %s DH',
                        $paiementsCreated,
                        number_format($montantTotal, 2)
                    ));
            } else {
                return redirect()
                    ->back()
                    ->with('info', 'Aucun nouveau paiement à calculer pour cette période.');
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', 'Erreur lors du calcul des paiements: ' . $e->getMessage());
        }
    }

    /**
     * Mark a specific member payment as paid.
     */
    public function marquerPaye($membreId, Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        $validated = $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date',
        ]);

        // Find the payment
        $paiement = PaiementCooperativeEleveur::where('id_membre', $membreId)
            ->where('id_cooperative', $cooperativeId)
            ->where('periode_debut', $validated['periode_debut'])
            ->where('periode_fin', $validated['periode_fin'])
            ->first();

        if (!$paiement) {
            return redirect()
                ->back()
                ->with('error', 'Paiement introuvable.');
        }

        if ($paiement->statut === 'paye') {
            return redirect()
                ->back()
                ->with('info', 'Ce paiement est déjà marqué comme payé.');
        }

        try {
            $paiement->marquerPaye();
            
            return redirect()
                ->back()
                ->with('success', sprintf(
                    'Paiement marqué comme payé ! Membre: %s - Montant: %s DH',
                    $paiement->membre->nom_complet,
                    number_format($paiement->montant_total, 2)
                ));
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Mark all pending payments as paid for the quinzaine.
     */
    public function marquerTousPayes(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        $validated = $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $paiements = PaiementCooperativeEleveur::where('id_cooperative', $cooperativeId)
                ->where('periode_debut', $validated['periode_debut'])
                ->where('periode_fin', $validated['periode_fin'])
                ->where('statut', 'calcule')
                ->get();

            if ($paiements->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('info', 'Aucun paiement en attente trouvé pour cette période.');
            }

            $count = 0;
            $montantTotal = 0;

            foreach ($paiements as $paiement) {
                $paiement->marquerPaye();
                $count++;
                $montantTotal += $paiement->montant_total;
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', sprintf(
                    'Tous les paiements marqués comme payés ! %d paiement(s) - Montant total: %s DH',
                    $count,
                    number_format($montantTotal, 2)
                ));
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }
}