<?php

namespace Database\Seeders;

use App\Models\LivraisonUsine;
use App\Models\PaiementCooperativeUsine;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaiementCooperativeUsineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Génération des paiements coopérative-usine basés sur les livraisons...');

        // Vérifier qu'il y a des livraisons
        $totalLivraisons = LivraisonUsine::count();
        if ($totalLivraisons === 0) {
            $this->command->warn('Aucune livraison trouvée. Veuillez d\'abord exécuter LivraisonUsineSeeder.');
            return;
        }

        $this->command->info("Livraisons trouvées: {$totalLivraisons}");

        // Récupérer les livraisons éligibles pour paiement (validées ou payées)
        $livraisonsEligibles = LivraisonUsine::whereIn('statut', ['validee', 'payee'])
            ->orderBy('date_livraison')
            ->get();

        if ($livraisonsEligibles->isEmpty()) {
            $this->command->warn('Aucune livraison validée ou payée trouvée.');
            return;
        }

        $this->command->info("Livraisons éligibles pour paiement: {$livraisonsEligibles->count()}");

        $totalPaiementsCreés = 0;
        $progressBar = $this->command->getOutput()->createProgressBar($livraisonsEligibles->count());
        $progressBar->start();

        foreach ($livraisonsEligibles as $livraison) {
            // Vérifier si le paiement existe déjà
            $paiementExistant = PaiementCooperativeUsine::where('id_livraison', $livraison->id_livraison)->exists();
            
            if (!$paiementExistant) {
                $paiement = $this->creerPaiementPourLivraison($livraison);
                if ($paiement) {
                    $totalPaiementsCreés++;
                }
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();

        $this->afficherStatistiques($totalPaiementsCreés);
    }

    /**
     * Créer un paiement pour une livraison donnée
     */
    private function creerPaiementPourLivraison(LivraisonUsine $livraison): ?PaiementCooperativeUsine
    {
        $dateLivraison = Carbon::parse($livraison->date_livraison);
        
        // Calculer la date de paiement et le statut
        $infoPaiement = $this->calculerInfoPaiement($livraison, $dateLivraison);
        
        if (!$infoPaiement) {
            return null;
        }

        // Ajuster le montant (frais, bonus, etc.)
        $montantAjuste = $this->ajusterMontant($livraison);

        // Créer le paiement
        $paiement = PaiementCooperativeUsine::create([
            'id_cooperative' => $livraison->id_cooperative,
            'id_livraison' => $livraison->id_livraison,
            'date_paiement' => $infoPaiement['date_paiement'],
            'montant' => $montantAjuste,
            'statut' => $infoPaiement['statut'],
            'created_at' => $infoPaiement['created_at'],
            'updated_at' => $infoPaiement['updated_at'],
        ]);

        return $paiement;
    }

    /**
     * Calculer les informations de paiement
     */
    private function calculerInfoPaiement(LivraisonUsine $livraison, Carbon $dateLivraison): ?array
    {
        // Délai de paiement selon la coopérative et les conditions
        $delaiPaiement = $this->calculerDelaiPaiement($livraison);
        
        // Date de paiement théorique
        $datePaiementTheorique = $dateLivraison->copy()->addDays($delaiPaiement);
        
        // Date de création du paiement (généralement quelques jours après la livraison)
        $dateCreation = $dateLivraison->copy()->addDays(rand(1, 7));

        // Déterminer le statut selon la date actuelle
        $joursDepuisPaiementTheorique = $datePaiementTheorique->diffInDays(now(), false);
        
        if ($joursDepuisPaiementTheorique > 0) {
            // Date de paiement dépassée
            $statut = $this->faker->randomElement([
                'paye' => 80,        // 80% sont effectivement payés
                'en_attente' => 20   // 20% en retard
            ]);
            
            if ($statut === 'paye') {
                // Paiement effectué avec un léger retard possible
                $datePaiementReelle = $datePaiementTheorique->copy()->addDays(rand(-2, 10));
                $dateMiseAJour = $datePaiementReelle->copy()->addHours(rand(1, 48));
            } else {
                // Toujours en attente
                $datePaiementReelle = $datePaiementTheorique;
                $dateMiseAJour = $dateCreation;
            }
        } else {
            // Date de paiement future
            $statut = 'en_attente';
            $datePaiementReelle = $datePaiementTheorique;
            $dateMiseAJour = $dateCreation;
        }

        return [
            'date_paiement' => $datePaiementReelle->format('Y-m-d'),
            'statut' => $statut,
            'created_at' => $dateCreation,
            'updated_at' => $dateMiseAJour,
        ];
    }

    /**
     * Calculer le délai de paiement réaliste
     */
    private function calculerDelaiPaiement(LivraisonUsine $livraison): int
    {
        // Récupérer les infos de la coopérative
        $cooperative = $livraison->cooperative;
        $nombreMembres = $cooperative->membres()->where('statut', 'actif')->count();
        
        // Volume moyen de la coopérative
        $volumeMoyen = LivraisonUsine::where('id_cooperative', $livraison->id_cooperative)
            ->avg('quantite_litres') ?? 0;

        // Délai de base selon la taille
        $delaiBase = match (true) {
            // Grandes coopératives : traitement prioritaire
            $nombreMembres >= 60 || $volumeMoyen >= 1000 => rand(15, 25),
            
            // Moyennes coopératives : délai standard
            $nombreMembres >= 25 || $volumeMoyen >= 500 => rand(25, 35),
            
            // Petites coopératives : délai plus long
            default => rand(35, 50)
        };

        // Facteur qualité (historique de la coopérative)
        $facteurQualite = $this->getFacteurQualiteCooperative($cooperative);
        
        // Facteur saisonnier (période de forte activité = délais plus longs)
        $moisLivraison = Carbon::parse($livraison->date_livraison)->month;
        $facteurSaisonnier = match (true) {
            in_array($moisLivraison, [3, 4, 5]) => 1.2,  // Printemps : +20% délai
            in_array($moisLivraison, [6, 7, 8]) => 0.9,  // Été : -10% délai
            default => 1.0
        };

        $delaiAjuste = (int) ($delaiBase * $facteurQualite * $facteurSaisonnier);
        
        return max(10, min(60, $delaiAjuste)); // Entre 10 et 60 jours
    }

    /**
     * Obtenir le facteur qualité d'une coopérative
     */
    private function getFacteurQualiteCooperative($cooperative): float
    {
        // Simuler un historique qualité basé sur l'ID (cohérent)
        $seed = $cooperative->id_cooperative;
        mt_srand($seed);
        
        $qualite = mt_rand(1, 100);
        
        return match (true) {
            $qualite <= 20 => 0.8,   // 20% - Coopératives premium (délai réduit)
            $qualite <= 60 => 1.0,   // 40% - Coopératives standard
            default => 1.3           // 40% - Coopératives avec historique difficile
        };
    }

    /**
     * Ajuster le montant par rapport à la livraison
     */
    private function ajusterMontant(LivraisonUsine $livraison): float
    {
        $montantBase = $livraison->montant_total;
        
        // Facteurs d'ajustement possibles
        $adjustements = [
            ['type' => 'standard', 'facteur' => 1.0, 'poids' => 60],      // 60% - Montant identique
            ['type' => 'frais_gestion', 'facteur' => 0.98, 'poids' => 15], // 15% - Frais de gestion (-2%)
            ['type' => 'penalite_qualite', 'facteur' => 0.95, 'poids' => 8], // 8% - Pénalité qualité (-5%)
            ['type' => 'bonus_qualite', 'facteur' => 1.03, 'poids' => 12], // 12% - Bonus qualité (+3%)
            ['type' => 'prime_performance', 'facteur' => 1.05, 'poids' => 5], // 5% - Prime performance (+5%)
        ];

        // Sélection pondérée
        $random = rand(1, 100);
        $cumul = 0;
        
        foreach ($adjustements as $adj) {
            $cumul += $adj['poids'];
            if ($random <= $cumul) {
                return round($montantBase * $adj['facteur'], 2);
            }
        }

        return $montantBase; // Fallback
    }

    /**
     * Instance de Faker pour les méthodes utilitaires
     */
    private $faker = null;
    
    private function faker()
    {
        if (!$this->faker) {
            $this->faker = \Faker\Factory::create('fr_FR');
        }
        return $this->faker;
    }

    /**
     * Afficher les statistiques de création
     */
    private function afficherStatistiques(int $totalPaiements): void
    {
        $this->command->info("✅ {$totalPaiements} paiements coopérative-usine créés");

        // Statistiques générales
        $stats = PaiementCooperativeUsine::selectRaw('
            COUNT(*) as total_paiements,
            SUM(montant) as total_montant,
            AVG(montant) as montant_moyen,
            MIN(date_paiement) as date_debut,
            MAX(date_paiement) as date_fin
        ')->first();

        $this->command->info("\n📊 Statistiques globales:");
        $this->command->info("   - Période: {$stats->date_debut} au {$stats->date_fin}");
        $this->command->info("   - Montant total: " . number_format($stats->total_montant, 2) . " DH");
        $this->command->info("   - Montant moyen: " . number_format($stats->montant_moyen, 2) . " DH/paiement");

        // Répartition par statut
        $this->command->info("\n📋 Répartition par statut:");
        $statutStats = PaiementCooperativeUsine::selectRaw('
            statut, 
            COUNT(*) as nombre, 
            SUM(montant) as montant_total,
            AVG(montant) as montant_moyen
        ')
        ->groupBy('statut')
        ->get();

        foreach ($statutStats as $stat) {
            $pourcentage = ($stat->nombre / $stats->total_paiements) * 100;
            $this->command->info(sprintf(
                "   - %s: %d paiements (%.1f%%) - %.2f DH total (%.2f DH/paiement)",
                ucfirst(str_replace('_', ' ', $stat->statut)),
                $stat->nombre,
                $pourcentage,
                $stat->montant_total,
                $stat->montant_moyen
            ));
        }

        // Paiements en retard
        $paiementsEnRetard = PaiementCooperativeUsine::where('statut', 'en_attente')
            ->where('date_paiement', '<', now())
            ->count();

        if ($paiementsEnRetard > 0) {
            $this->command->warn("⚠️  {$paiementsEnRetard} paiements en retard détectés");
        }

        // Top 5 des plus gros paiements
        $this->command->info("\n💰 Top 5 des plus gros paiements:");
        $topPaiements = DB::table('paiements_cooperative_usine')
            ->join('cooperatives', 'paiements_cooperative_usine.id_cooperative', '=', 'cooperatives.id_cooperative')
            ->join('livraisons_usine', 'paiements_cooperative_usine.id_livraison', '=', 'livraisons_usine.id_livraison')
            ->select(
                'cooperatives.nom_cooperative',
                'paiements_cooperative_usine.date_paiement',
                'paiements_cooperative_usine.montant',
                'paiements_cooperative_usine.statut',
                'livraisons_usine.quantite_litres'
            )
            ->orderBy('paiements_cooperative_usine.montant', 'DESC')
            ->limit(5)
            ->get();

        foreach ($topPaiements as $index => $paiement) {
            $this->command->info(sprintf(
                "   %d. %s - %s: %.2f DH (%.2f L) [%s]",
                $index + 1,
                $paiement->nom_cooperative,
                Carbon::parse($paiement->date_paiement)->format('d/m/Y'),
                $paiement->montant,
                $paiement->quantite_litres,
                $paiement->statut
            ));
        }

        // Répartition par coopérative
        $this->command->info("\n🏭 Répartition par coopérative:");
        $coopStats = DB::table('paiements_cooperative_usine')
            ->join('cooperatives', 'paiements_cooperative_usine.id_cooperative', '=', 'cooperatives.id_cooperative')
            ->selectRaw('
                cooperatives.nom_cooperative,
                COUNT(*) as nombre_paiements,
                SUM(paiements_cooperative_usine.montant) as total_montant,
                AVG(paiements_cooperative_usine.montant) as montant_moyen,
                SUM(CASE WHEN paiements_cooperative_usine.statut = "paye" THEN 1 ELSE 0 END) as payes,
                SUM(CASE WHEN paiements_cooperative_usine.statut = "en_attente" THEN 1 ELSE 0 END) as en_attente
            ')
            ->groupBy('cooperatives.id_cooperative', 'cooperatives.nom_cooperative')
            ->orderBy('total_montant', 'DESC')
            ->get();

        foreach ($coopStats as $coop) {
            $tauxPaiement = $coop->nombre_paiements > 0 ? ($coop->payes / $coop->nombre_paiements) * 100 : 0;
            $this->command->info(sprintf(
                "   - %s: %d paiements, %.2f DH total (%.1f%% payés)",
                $coop->nom_cooperative,
                $coop->nombre_paiements,
                $coop->total_montant,
                $tauxPaiement
            ));
        }

        // Délais de paiement
        $this->command->info("\n⏱️  Analyse des délais de paiement:");
        $delaiStats = DB::select("
            SELECT 
                AVG(DATEDIFF(p.date_paiement, l.date_livraison)) as delai_moyen,
                MIN(DATEDIFF(p.date_paiement, l.date_livraison)) as delai_min,
                MAX(DATEDIFF(p.date_paiement, l.date_livraison)) as delai_max
            FROM paiements_cooperative_usine p
            JOIN livraisons_usine l ON p.id_livraison = l.id_livraison
            WHERE p.statut = 'paye'
        ");

        if (!empty($delaiStats)) {
            $delai = $delaiStats[0];
            $this->command->info("   - Délai moyen: " . round($delai->delai_moyen, 1) . " jours");
            $this->command->info("   - Délai minimum: {$delai->delai_min} jours");
            $this->command->info("   - Délai maximum: {$delai->delai_max} jours");
        }
    }
}