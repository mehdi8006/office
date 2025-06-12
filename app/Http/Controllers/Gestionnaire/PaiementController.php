<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\PaiementCooperativeUsine;
use App\Models\LivraisonUsine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PaiementController extends Controller
{
    /**
     * Prix unitaire par défaut (configurable)
     */
    const PRIX_UNITAIRE_DEFAULT = 3.50;

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
     * Display current quinzaine with payment status.
     */
    public function index(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        // Calculate prix unitaire (dernière valeur utilisée ou défaut)
        $dernierPrix = PaiementCooperativeUsine::where('id_cooperative', $cooperativeId)
            ->orderBy('created_at', 'desc')
            ->value('prix_unitaire');
        $prixUnitaire = $dernierPrix ?? self::PRIX_UNITAIRE_DEFAULT;
        
        // Get current quinzaine data
        $quinzaineActuelle = $this->getCurrentQuinzaineData($cooperativeId, $prixUnitaire);
        
        // Get all pending payments (toutes les quinzaines en attente, pas seulement le mois actuel)
        $paiementsEnAttente = $this->getAllPaiementsEnAttente($cooperativeId);
        
        return view('gestionnaire.paiements.index', compact(
            'quinzaineActuelle', 
            'paiementsEnAttente',
            'cooperative', 
            'prixUnitaire'
        ));
    }

    /**
     * Determine current quinzaine and return its data.
     */
    private function getCurrentQuinzaineData($cooperativeId, $prixUnitaire)
    {
        $today = now();
        $currentDay = $today->day;
        
        // Determine current quinzaine
        if ($currentDay <= 15) {
            // Quinzaine 1: 1-15
            $dateDebut = $today->copy()->startOfMonth();
            $dateFin = $today->copy()->startOfMonth()->addDays(14);
            $label = "1-15 " . $today->translatedFormat('F Y');
        } else {
            // Quinzaine 2: 16-fin du mois
            $dateDebut = $today->copy()->startOfMonth()->addDays(15);
            $dateFin = $today->copy()->endOfMonth();
            $label = "16-" . $dateFin->day . " " . $today->translatedFormat('F Y');
        }
        
        return $this->calculateQuinzaineData(
            $cooperativeId,
            $dateDebut,
            $dateFin,
            $label,
            $prixUnitaire,
            true // isCurrentQuinzaine
        );
    }

    /**
     * Calculate data for a specific quinzaine.
     */
    private function calculateQuinzaineData($cooperativeId, $dateDebut, $dateFin, $label, $prixUnitaire, $isCurrentQuinzaine = false)
    {
        // Get validated livraisons for this period
        $livraisons = LivraisonUsine::where('id_cooperative', $cooperativeId)
            ->where('statut', 'validee')
            ->whereBetween('date_livraison', [$dateDebut, $dateFin])
            ->get();

        $totalQuantite = $livraisons->sum('quantite_litres');
        $montantCalcule = $totalQuantite * $prixUnitaire;

        // Check existing payment for this quinzaine
        $paiementExistant = PaiementCooperativeUsine::where('id_cooperative', $cooperativeId)
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->first();

        // Determine status
        $estTerminee = $dateFin->isPast();
        $statut = 'non_calcule';
        $statutColor = 'secondary';
        $peutCalculer = false;
        $montantPaye = 0;
        $messageSpecial = null;

        if ($paiementExistant) {
            $montantCalcule = $paiementExistant->montant;
            if ($paiementExistant->statut === 'paye') {
                $statut = 'paye';
                $statutColor = 'success';
                $montantPaye = $paiementExistant->montant;
            } else {
                $statut = 'en_attente';
                $statutColor = 'warning';
            }
        } else {
            if ($isCurrentQuinzaine && !$estTerminee) {
                // Quinzaine actuelle en cours
                $statut = 'en_cours';
                $statutColor = 'info';
                $messageSpecial = 'Période pas finie';
            } elseif ($totalQuantite > 0 && $estTerminee) {
                // Quinzaine terminée avec des livraisons, peut être calculée
                $statut = 'non_calcule';
                $statutColor = 'danger';
                $peutCalculer = true;
            }
        }

        return [
            'label' => $label,
            'date_debut' => $dateDebut->format('Y-m-d'),
            'date_fin' => $dateFin->format('Y-m-d'),
            'total_quantite' => $totalQuantite,
            'montant_calcule' => $montantCalcule,
            'montant_paye' => $montantPaye,
            'statut' => $statut,
            'statut_color' => $statutColor,
            'peut_calculer' => $peutCalculer,
            'est_terminee' => $estTerminee,
            'est_en_cours' => $isCurrentQuinzaine && !$estTerminee,
            'message_special' => $messageSpecial,
            'paiement' => $paiementExistant,
        ];
    }

    /**
     * Get all pending payments (from all months).
     */
    private function getAllPaiementsEnAttente($cooperativeId)
    {
        return PaiementCooperativeUsine::where('id_cooperative', $cooperativeId)
            ->where('statut', 'en_attente')
            ->orderBy('date_paiement', 'desc')
            ->get();
    }

    /**
     * Calculate payments for a quinzaine (ONLY action available to gestionnaire).
     */
    public function calculerQuinzaine(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'prix_unitaire' => 'required|numeric|min:0.1|max:999.99',
        ], [
            'date_debut.required' => 'La date de début est requise',
            'date_fin.required' => 'La date de fin est requise',
            'prix_unitaire.required' => 'Le prix unitaire est requis',
            'prix_unitaire.numeric' => 'Le prix unitaire doit être un nombre',
        ]);

        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($validated['date_debut']);
            $endDate = Carbon::parse($validated['date_fin']);
            $prixUnitaire = $validated['prix_unitaire'];

            // Check if payment already exists
            $existingPaiement = PaiementCooperativeUsine::where('id_cooperative', $cooperativeId)
                ->whereBetween('date_paiement', [$startDate, $endDate])
                ->first();
            
            if ($existingPaiement) {
                return redirect()
                    ->back()
                    ->with('info', 'Un paiement a déjà été calculé pour cette quinzaine.');
            }

            // Verify quinzaine is finished
            if ($endDate->isFuture()) {
                return redirect()
                    ->back()
                    ->with('error', 'Impossible de calculer une quinzaine en cours. Attendez la fin de la période.');
            }

            // Get validated livraisons for this quinzaine
            $livraisons = LivraisonUsine::where('id_cooperative', $cooperativeId)
                ->where('statut', 'validee')
                ->whereBetween('date_livraison', [$startDate, $endDate])
                ->get();

            if ($livraisons->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'Aucune livraison validée trouvée pour cette quinzaine.');
            }

            $quantiteTotale = $livraisons->sum('quantite_litres');

            // Create quinzaine payment
            $paiement = PaiementCooperativeUsine::creerPaiementQuinzaine(
                $cooperativeId,
                $startDate,
                $endDate,
                $quantiteTotale,
                $prixUnitaire
            );

            DB::commit();

            return redirect()
                ->back()
                ->with('success', sprintf(
                    'Paiement quinzaine calculé avec succès ! Quantité: %s L - Montant: %s DH',
                    number_format($quantiteTotale, 2),
                    number_format($paiement->montant, 2)
                ));
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->with('error', 'Erreur lors du calcul: ' . $e->getMessage());
        }
    }

    /**
     * Download paid quinzaines history as PDF.
     */
    public function downloadHistoriqueQuinzaines(Request $request)
    {
        $cooperativeId = $this->getCurrentCooperativeId();
        $cooperative = $this->getCurrentCooperative();
        
        $validated = $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
        ]);

        try {
            // Get paid payments (if dates specified, filter by them)
            $query = PaiementCooperativeUsine::where('id_cooperative', $cooperativeId)
                ->where('statut', 'paye')
                ->orderBy('date_paiement', 'desc');

            if (isset($validated['date_debut']) && isset($validated['date_fin'])) {
                $query->whereBetween('date_paiement', [$validated['date_debut'], $validated['date_fin']]);
            }

            $paiements = $query->get();

            if ($paiements->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'Aucune quinzaine payée trouvée pour la période sélectionnée.');
            }

            // Calculate statistics
            $stats = [
                'total_quinzaines' => $paiements->count(),
                'total_quantite' => $paiements->sum('quantite_litres'),
                'total_montant' => $paiements->sum('montant'),
                'prix_moyen' => $paiements->avg('prix_unitaire'),
                'premiere_quinzaine' => $paiements->last()?->date_paiement,
                'derniere_quinzaine' => $paiements->first()?->date_paiement,
            ];

            // Generate PDF
            $pdf = Pdf::loadView('gestionnaire.paiements.exports.historique-quinzaines-pdf', compact(
                'cooperative', 
                'paiements', 
                'stats'
            ));
            
            $filename = sprintf(
                'Historique_Quinzaines_%s_%s.pdf',
                str_replace(' ', '_', $cooperative->nom_cooperative),
                now()->format('Y-m-d')
            );
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }
}