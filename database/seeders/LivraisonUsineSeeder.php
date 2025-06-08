<?php

namespace Database\Seeders;

use App\Models\Cooperative;
use App\Models\StockLait;
use App\Models\LivraisonUsine;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LivraisonUsineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Génération des livraisons à l\'usine basées sur les stocks...');

        // Vérifier qu'il y a des stocks
        $totalStocks = StockLait::count();
        if ($totalStocks === 0) {
            $this->command->warn('Aucun stock de lait trouvé. Veuillez d\'abord exécuter StockLaitSeeder.');
            return;
        }

        $this->command->info("Stocks trouvés: {$totalStocks}");

        // Récupérer toutes les coopératives qui ont des stocks
        $cooperatives = Cooperative::whereHas('stocks')->get();
        
        if ($cooperatives->isEmpty()) {
            $this->command->warn('Aucune coopérative avec des stocks trouvée.');
            return;
        }

        $this->command->info("Coopératives avec stocks: {$cooperatives->count()}");

        $totalLivraisonsCreees = 0;
        
        foreach ($cooperatives as $cooperative) {
            $this->command->info("Traitement de la coopérative: {$cooperative->nom_cooperative}");
            
            $livraisonsCreees = $this->genererLivraisonsPourCooperative($cooperative);
            $totalLivraisonsCreees += $livraisonsCreees;
        }

        $this->afficherStatistiques($totalLivraisonsCreees);
    }

    /**
     * Générer les livraisons pour une coopérative donnée
     */
    private function genererLivraisonsPourCooperative(Cooperative $cooperative): int
    {
        // Récupérer tous les stocks avec des quantités livrées pour cette coopérative
        $stocks = StockLait::where('id_cooperative', $cooperative->id_cooperative)
            ->where('quantite_livree', '>', 0)
            ->orderBy('date_stock')
            ->get();

        if ($stocks->isEmpty()) {
            return 0;
        }

        $livraisonsCreees = 0;
        
        // Stratégie de livraison par coopérative
        $strategieLivraison = $this->determinerStrategieLivraison($cooperative);

        foreach ($stocks as $stock) {
            // Décider si cette quantité livrée génère une livraison
            if ($this->doitCreerLivraison($stock, $strategieLivraison)) {
                $livraison = $this->creerLivraison($stock, $strategieLivraison);
                if ($livraison) {
                    $livraisonsCreees++;
                }
            }
        }

        return $livraisonsCreees;
    }

    /**
     * Déterminer la stratégie de livraison d'une coopérative
     */
    private function determinerStrategieLivraison(Cooperative $cooperative): array
    {
        // Nombre de membres actifs pour déterminer la taille
        $nombreMembres = $cooperative->membres()->where('statut', 'actif')->count();
        
        // Volume moyen pour déterminer la capacité
        $volumeMoyen = StockLait::where('id_cooperative', $cooperative->id_cooperative)
            ->avg('quantite_totale') ?? 0;

        // Stratégies selon la taille et le volume
        return match (true) {
            // Petites coopératives (moins de 25 membres, volume < 300L)
            $nombreMembres < 25 && $volumeMoyen < 300 => [
                'frequence' => 'groupee',        // Livraisons groupées
                'seuil_minimum' => 500,          // Attendre 500L minimum
                'probabilite_livraison' => 40,   // 40% de chance par jour éligible
                'delai_validation' => [1, 5],    // Validation 1-5 jours
                'delai_paiement' => [20, 45],    // Paiement 20-45 jours
            ],
            
            // Moyennes coopératives (25-60 membres, volume 300-800L)
            $nombreMembres < 60 && $volumeMoyen < 800 => [
                'frequence' => 'reguliere',
                'seuil_minimum' => 300,
                'probabilite_livraison' => 65,
                'delai_validation' => [1, 3],
                'delai_paiement' => [15, 35],
            ],
            
            // Grandes coopératives (60+ membres ou volume 800L+)
            default => [
                'frequence' => 'quotidienne',
                'seuil_minimum' => 200,
                'probabilite_livraison' => 85,
                'delai_validation' => [0, 2],
                'delai_paiement' => [10, 30],
            ]
        };
    }

    /**
     * Décider si un stock doit générer une livraison
     */
    private function doitCreerLivraison(StockLait $stock, array $strategie): bool
    {
        // Vérifier le seuil minimum
        if ($stock->quantite_livree < $strategie['seuil_minimum']) {
            return false;
        }

        // Vérifier si livraison déjà créée
        $livraisonExistante = LivraisonUsine::where('id_cooperative', $stock->id_cooperative)
            ->where('date_livraison', $stock->date_stock)
            ->exists();

        if ($livraisonExistante) {
            return false;
        }

        // Facteur jour de la semaine
        $dateStock = Carbon::parse($stock->date_stock);
        $facteurJour = match ($dateStock->dayOfWeek) {
            Carbon::MONDAY, Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY, Carbon::FRIDAY => 1.0,
            Carbon::SATURDAY => 0.6,  // Moins de livraisons le samedi
            Carbon::SUNDAY => 0.2     // Très peu le dimanche
        };

        // Probabilité finale
        $probabiliteFinal = $strategie['probabilite_livraison'] * $facteurJour;
        
        return rand(1, 100) <= $probabiliteFinal;
    }

    /**
     * Créer une livraison basée sur un stock
     */
    private function creerLivraison(StockLait $stock, array $strategie): ?LivraisonUsine
    {
        $dateLivraison = Carbon::parse($stock->date_stock);
        
        // Quantité livrée (avec petite variation par rapport au stock)
        $variationQuantite = rand(95, 105) / 100; // ±5%
        $quantiteLitres = round($stock->quantite_livree * $variationQuantite, 2);

        // Prix unitaire réaliste selon la saison
        $prixUnitaire = $this->calculerPrixUnitaire($dateLivraison);
        
        // Montant total
        $montantTotal = round($quantiteLitres * $prixUnitaire, 2);

        // Statut initial
        $statut = $this->determinerStatutInitial($dateLivraison);

        // Créer la livraison
        $livraison = LivraisonUsine::create([
            'id_cooperative' => $stock->id_cooperative,
            'date_livraison' => $stock->date_stock,
            'quantite_litres' => $quantiteLitres,
            'prix_unitaire' => $prixUnitaire,
            'montant_total' => $montantTotal,
            'statut' => $statut,
            'created_at' => $dateLivraison,
            'updated_at' => $this->calculerDateMiseAJour($dateLivraison, $statut, $strategie),
        ]);

        return $livraison;
    }

    /**
     * Calculer le prix unitaire selon la saison
     */
    private function calculerPrixUnitaire(Carbon $date): float
    {
        $mois = $date->month;
        
        // Prix de base selon la saison (marché marocain)
        $prixBase = match (true) {
            // Printemps : Haute production, prix plus bas
            in_array($mois, [3, 4, 5]) => rand(400, 480) / 100, // 4.00-4.80 DH
            
            // Été : Production réduite, prix plus élevé  
            in_array($mois, [6, 7, 8]) => rand(520, 600) / 100, // 5.20-6.00 DH
            
            // Automne : Production moyenne, prix stable
            in_array($mois, [9, 10, 11]) => rand(450, 550) / 100, // 4.50-5.50 DH
            
            // Hiver : Production normale, prix moyen
            default => rand(480, 530) / 100 // 4.80-5.30 DH
        };

        // Facteur qualité (aléatoire par coopérative)
        $facteurQualite = match (rand(1, 100)) {
            1, 2, 3, 4, 5 => 0.95,           // 5% - Qualité inférieure (-5%)
            6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30 => 1.05, // 25% - Prime qualité (+5%)
            31, 32, 33, 34, 35, 36, 37, 38, 39, 40 => 1.10, // 10% - Excellente qualité (+10%)
            default => 1.0                   // 60% - Prix standard
        };

        return round($prixBase * $facteurQualite, 2);
    }

    /**
     * Déterminer le statut initial selon l'ancienneté
     */
    private function determinerStatutInitial(Carbon $dateLivraison): string
    {
        $joursDepuis = $dateLivraison->diffInDays(now());
        
        return match (true) {
            $joursDepuis < 7 => 'planifiee',     // Récent = planifiée
            $joursDepuis < 30 => 'validee',      // Moyen = validée  
            default => 'payee'                   // Ancien = payée
        };
    }

    /**
     * Calculer la date de mise à jour selon le statut
     */
    private function calculerDateMiseAJour(Carbon $dateLivraison, string $statut, array $strategie): Carbon
    {
        return match ($statut) {
            'planifiee' => $dateLivraison->copy(),
            'validee' => $dateLivraison->copy()->addDays(rand($strategie['delai_validation'][0], $strategie['delai_validation'][1])),
            'payee' => $dateLivraison->copy()->addDays(rand($strategie['delai_paiement'][0], $strategie['delai_paiement'][1])),
            default => $dateLivraison->copy()
        };
    }

    /**
     * Afficher les statistiques de création
     */
    private function afficherStatistiques(int $totalLivraisons): void
    {
        $this->command->info("✅ {$totalLivraisons} livraisons à l'usine créées");

        // Statistiques générales
        $stats = LivraisonUsine::selectRaw('
            COUNT(*) as total_livraisons,
            SUM(quantite_litres) as total_litres,
            SUM(montant_total) as total_montant,
            AVG(quantite_litres) as moyenne_quantite,
            AVG(prix_unitaire) as prix_moyen,
            MIN(date_livraison) as date_debut,
            MAX(date_livraison) as date_fin
        ')->first();

        $this->command->info("\n📊 Statistiques globales:");
        $this->command->info("   - Période: {$stats->date_debut} au {$stats->date_fin}");
        $this->command->info("   - Total livré: " . number_format($stats->total_litres, 2) . " litres");
        $this->command->info("   - Montant total: " . number_format($stats->total_montant, 2) . " DH");
        $this->command->info("   - Quantité moyenne: " . number_format($stats->moyenne_quantite, 2) . " L/livraison");
        $this->command->info("   - Prix moyen: " . number_format($stats->prix_moyen, 2) . " DH/L");

        // Répartition par statut
        $this->command->info("\n📋 Répartition par statut:");
        $statutStats = LivraisonUsine::selectRaw('statut, COUNT(*) as nombre, SUM(montant_total) as montant')
            ->groupBy('statut')
            ->get();

        foreach ($statutStats as $stat) {
            $this->command->info(sprintf(
                "   - %s: %d livraisons (%.2f DH)",
                ucfirst($stat->statut),
                $stat->nombre,
                $stat->montant
            ));
        }

        // Top 5 des plus grosses livraisons
        $this->command->info("\n🚛 Top 5 des plus grosses livraisons:");
        $topLivraisons = DB::table('livraisons_usine')
            ->join('cooperatives', 'livraisons_usine.id_cooperative', '=', 'cooperatives.id_cooperative')
            ->select('cooperatives.nom_cooperative', 'livraisons_usine.date_livraison', 
                    'livraisons_usine.quantite_litres', 'livraisons_usine.montant_total')
            ->orderBy('livraisons_usine.quantite_litres', 'DESC')
            ->limit(5)
            ->get();

        foreach ($topLivraisons as $index => $livraison) {
            $this->command->info(sprintf(
                "   %d. %s - %s: %.2f L (%.2f DH)",
                $index + 1,
                $livraison->nom_cooperative,
                Carbon::parse($livraison->date_livraison)->format('d/m/Y'),
                $livraison->quantite_litres,
                $livraison->montant_total
            ));
        }

        // Répartition par coopérative
        $this->command->info("\n🏭 Répartition par coopérative:");
        $coopStats = DB::table('livraisons_usine')
            ->join('cooperatives', 'livraisons_usine.id_cooperative', '=', 'cooperatives.id_cooperative')
            ->selectRaw('
                cooperatives.nom_cooperative,
                COUNT(*) as nombre_livraisons,
                SUM(livraisons_usine.quantite_litres) as total_litres,
                SUM(livraisons_usine.montant_total) as total_montant
            ')
            ->groupBy('cooperatives.id_cooperative', 'cooperatives.nom_cooperative')
            ->orderBy('total_montant', 'DESC')
            ->get();

        foreach ($coopStats as $coop) {
            $this->command->info(sprintf(
                "   - %s: %d livraisons, %.2f L, %.2f DH",
                $coop->nom_cooperative,
                $coop->nombre_livraisons,
                $coop->total_litres,
                $coop->total_montant
            ));
        }
    }
}