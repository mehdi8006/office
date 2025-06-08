<?php

namespace Database\Seeders;

use App\Models\Cooperative;
use App\Models\ReceptionLait;
use App\Models\StockLait;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockLaitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Génération des stocks de lait basés sur les réceptions...');

        // Vérifier qu'il y a des réceptions
        $totalReceptions = ReceptionLait::count();
        if ($totalReceptions === 0) {
            $this->command->warn('Aucune réception de lait trouvée. Veuillez d\'abord exécuter ReceptionLaitSeeder.');
            return;
        }

        $this->command->info("Réceptions trouvées: {$totalReceptions}");

        // Récupérer toutes les coopératives qui ont des réceptions
        $cooperatives = Cooperative::whereHas('receptions')->get();
        
        if ($cooperatives->isEmpty()) {
            $this->command->warn('Aucune coopérative avec des réceptions trouvée.');
            return;
        }

        $this->command->info("Coopératives avec réceptions: {$cooperatives->count()}");

        $totalStocksCreés = 0;
        
        foreach ($cooperatives as $cooperative) {
            $this->command->info("Traitement de la coopérative: {$cooperative->nom_cooperative}");
            
            $stocksCreés = $this->genererStocksPourCooperative($cooperative);
            $totalStocksCreés += $stocksCreés;
        }

        $this->afficherStatistiques($totalStocksCreés);
    }

    /**
     * Générer les stocks pour une coopérative donnée
     */
    private function genererStocksPourCooperative(Cooperative $cooperative): int
    {
        // Récupérer toutes les dates de réception pour cette coopérative
        $datesReception = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
            ->distinct()
            ->orderBy('date_reception')
            ->pluck('date_reception');

        if ($datesReception->isEmpty()) {
            return 0;
        }

        $stocksCreés = 0;
        $reportPrecedent = 0;

        foreach ($datesReception as $date) {
            // Calculer le stock pour cette date
            $stock = $this->calculerStockPourDate($cooperative, $date, $reportPrecedent);
            
            if ($stock) {
                $stocksCreés++;
                // Le report pour le jour suivant est la quantité disponible
                $reportPrecedent = $stock->quantite_disponible;
            }
        }

        return $stocksCreés;
    }

    /**
     * Calculer le stock pour une date et coopérative donnée
     */
    private function calculerStockPourDate(Cooperative $cooperative, string $date, float $reportPrecedent): ?StockLait
    {
        // Vérifier si le stock existe déjà
        $stockExistant = StockLait::where('id_cooperative', $cooperative->id_cooperative)
            ->where('date_stock', $date)
            ->first();

        if ($stockExistant) {
            return $stockExistant;
        }

        // Calculer la quantité totale reçue ce jour-là
        $quantiteRecue = ReceptionLait::where('id_cooperative', $cooperative->id_cooperative)
            ->where('date_reception', $date)
            ->sum('quantite_litres');

        // Quantité totale = reçue + report du jour précédent
        $quantiteTotale = round($quantiteRecue + $reportPrecedent, 2);

        // Si pas de quantité, ne pas créer de stock
        if ($quantiteTotale <= 0) {
            return null;
        }

        // Calculer la stratégie de livraison
        $resultatsLivraison = $this->calculerLivraison($quantiteTotale, $cooperative, $date);

        // Créer le stock
        $stock = StockLait::create([
            'id_cooperative' => $cooperative->id_cooperative,
            'date_stock' => $date,
            'quantite_totale' => $quantiteTotale,
            'quantite_livree' => $resultatsLivraison['livree'],
            'quantite_disponible' => $resultatsLivraison['disponible'],
            'created_at' => Carbon::parse($date),
            'updated_at' => Carbon::parse($date),
        ]);

        return $stock;
    }

    /**
     * Calculer la stratégie de livraison
     */
    private function calculerLivraison(float $quantiteTotale, Cooperative $cooperative, string $date): array
    {
        $dateCarbon = Carbon::parse($date);
        
        // Facteur jour de la semaine
        $facteurJour = match ($dateCarbon->dayOfWeek) {
            Carbon::MONDAY, Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY, Carbon::FRIDAY => 1.0,
            Carbon::SATURDAY => 0.7,  // Livraison réduite le samedi
            Carbon::SUNDAY => 0.3     // Très peu de livraisons le dimanche
        };

        // Facteur selon la taille de la coopérative (basé sur le nombre de membres)
        $nombreMembres = $cooperative->membres()->where('statut', 'actif')->count();
        $facteurTaille = match (true) {
            $nombreMembres < 20 => 0.65,    // Petites coopératives
            $nombreMembres < 50 => 0.75,    // Moyennes coopératives  
            default => 0.85                 // Grandes coopératives
        };

        // Facteur selon la quantité (les grosses quantités sont plus facilement livrées)
        $facteurQuantite = match (true) {
            $quantiteTotale < 200 => 0.6,
            $quantiteTotale < 500 => 0.75,
            $quantiteTotale < 1000 => 0.85,
            default => 0.9
        };

        // Variation aléatoire pour simuler les aléas logistiques
        $variationAleatoire = rand(85, 105) / 100;

        // Calcul du pourcentage de livraison
        $pourcentageLivraison = min(0.95, $facteurJour * $facteurTaille * $facteurQuantite * $variationAleatoire);

        $quantiteLivree = round($quantiteTotale * $pourcentageLivraison, 2);
        $quantiteDisponible = round($quantiteTotale - $quantiteLivree, 2);

        return [
            'livree' => $quantiteLivree,
            'disponible' => max(0, $quantiteDisponible)
        ];
    }

    /**
     * Afficher les statistiques de création
     */
    private function afficherStatistiques(int $totalStocks): void
    {
        $this->command->info("✅ {$totalStocks} entrées de stock créées");

        // Statistiques générales
        $stats = StockLait::selectRaw('
            COUNT(*) as total_jours,
            SUM(quantite_totale) as total_litres,
            SUM(quantite_livree) as total_livre,
            SUM(quantite_disponible) as total_disponible,
            AVG(quantite_totale) as moyenne_quotidienne,
            MIN(date_stock) as date_debut,
            MAX(date_stock) as date_fin
        ')->first();

        $this->command->info("\n📊 Statistiques globales:");
        $this->command->info("   - Période: {$stats->date_debut} au {$stats->date_fin}");
        $this->command->info("   - Total des litres traités: " . number_format($stats->total_litres, 2) . " L");
        $this->command->info("   - Total livré: " . number_format($stats->total_livre, 2) . " L");
        $this->command->info("   - Total disponible: " . number_format($stats->total_disponible, 2) . " L");
        $this->command->info("   - Moyenne quotidienne: " . number_format($stats->moyenne_quotidienne, 2) . " L/jour");

        // Taux de livraison
        $tauxLivraison = ($stats->total_livre / $stats->total_litres) * 100;
        $this->command->info("   - Taux de livraison: " . number_format($tauxLivraison, 1) . "%");

        // Statistiques par coopérative
        $this->command->info("\n🏭 Répartition par coopérative:");
        $statsCooperatives = DB::table('stock_lait')
            ->join('cooperatives', 'stock_lait.id_cooperative', '=', 'cooperatives.id_cooperative')
            ->selectRaw('
                cooperatives.nom_cooperative,
                COUNT(*) as jours_stock,
                SUM(stock_lait.quantite_totale) as total_litres,
                AVG(stock_lait.quantite_totale) as moyenne_jour
            ')
            ->groupBy('cooperatives.id_cooperative', 'cooperatives.nom_cooperative')
            ->orderBy('total_litres', 'DESC')
            ->get();

        foreach ($statsCooperatives as $coop) {
            $this->command->info(sprintf(
                "   - %s: %d jours, %.2f L total (%.2f L/jour)",
                $coop->nom_cooperative,
                $coop->jours_stock,
                $coop->total_litres,
                $coop->moyenne_jour
            ));
        }

        // Top 5 des plus gros volumes quotidiens
        $this->command->info("\n🥛 Top 5 des plus gros volumes quotidiens:");
        $topVolumes = StockLait::join('cooperatives', 'stock_lait.id_cooperative', '=', 'cooperatives.id_cooperative')
            ->select('cooperatives.nom_cooperative', 'stock_lait.date_stock', 'stock_lait.quantite_totale')
            ->orderBy('stock_lait.quantite_totale', 'DESC')
            ->limit(5)
            ->get();

        foreach ($topVolumes as $index => $top) {
            $this->command->info(sprintf(
                "   %d. %s - %s: %.2f L",
                $index + 1,
                $top->nom_cooperative,
                Carbon::parse($top->date_stock)->format('d/m/Y'),
                $top->quantite_totale
            ));
        }
    }
}