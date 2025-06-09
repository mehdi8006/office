<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Cooperative;
use App\Models\MembreEleveur;
use App\Models\ReceptionLait;
use App\Models\StockLait;
use App\Models\LivraisonUsine;
use App\Models\PaiementCooperativeUsine;
use App\Models\PaiementCooperativeEleveur;
use Carbon\Carbon;

/**
 * Seeder pour les données opérationnelles
 * (Réceptions, Stocks, Livraisons, Paiements)
 */
class OperationalDataSeeder extends Seeder
{
    private $dateDebut;
    private $dateFin;
    private $periodeEnMois;

    public function __construct()
    {
        $this->periodeEnMois = 6; // Par défaut 6 mois
        $this->dateFin = Carbon::now();
        $this->dateDebut = $this->dateFin->copy()->subMonths($this->periodeEnMois);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("🔄 Génération des données opérationnelles ({$this->periodeEnMois} mois)...");
        $this->command->info("   📅 Période: {$this->dateDebut->format('d/m/Y')} → {$this->dateFin->format('d/m/Y')}");

        // Vérifier que les données de base existent
        if (!$this->checkBaseData()) {
            $this->command->error('❌ Données de base manquantes. Exécutez d\'abord BaseDataSeeder.');
            return;
        }

        $cooperatives = Cooperative::with('membresActifs')->get();

        // 1. Générer les réceptions de lait
        $this->generateReceptions($cooperatives);

        // 2. Mettre à jour les stocks
        $this->updateStocks($cooperatives);

        // 3. Créer les livraisons usine
        $this->generateLivraisons($cooperatives);

        // 4. Créer les paiements usine
        $this->generatePaiementsUsine();

        // 5. Créer les paiements éleveurs
        $this->generatePaiementsEleveurs($cooperatives);

        $this->command->info('✅ Données opérationnelles générées avec succès!');
        $this->printOperationalStats();
    }

    /**
     * Définir une période personnalisée
     */
    public function setPeriode(int $mois): self
    {
        $this->periodeEnMois = $mois;
        $this->dateFin = Carbon::now();
        $this->dateDebut = $this->dateFin->copy()->subMonths($mois);
        return $this;
    }

    /**
     * Vérifier que les données de base existent
     */
    private function checkBaseData(): bool
    {
        $cooperatives = Cooperative::count();
        $membres = MembreEleveur::where('statut', 'actif')->count();
        
        return $cooperatives > 0 && $membres > 0;
    }

    /**
     * Générer les réceptions de lait
     */
    private function generateReceptions($cooperatives): void
    {
        $this->command->info('   🥛 Génération des réceptions...');
        
        $totalReceptions = 0;
        $batchSize = 500;
        $receptionsBatch = [];

        foreach ($cooperatives as $cooperative) {
            $membresActifs = $cooperative->membresActifs;
            
            if ($membresActifs->isEmpty()) {
                continue;
            }

            foreach ($membresActifs as $membre) {
                $currentDate = $this->dateDebut->copy();
                
                while ($currentDate <= $this->dateFin) {
                    // Logique de probabilité selon le jour
                    $probabilite = $this->getProbabiliteReception($currentDate);
                    
                    if (fake()->boolean($probabilite)) {
                        $receptionsBatch[] = [
                            'id_cooperative' => $membre->id_cooperative,
                            'id_membre' => $membre->id_membre,
                            'matricule_reception' => $this->generateMatriculeReception($currentDate),
                            'date_reception' => $currentDate->format('Y-m-d'),
                            'quantite_litres' => $this->generateQuantiteReception($currentDate, $membre),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $totalReceptions++;

                        // Insérer par batch pour performance
                        if (count($receptionsBatch) >= $batchSize) {
                            DB::table('receptions_lait')->insert($receptionsBatch);
                            $receptionsBatch = [];
                        }
                    }

                    $currentDate->addDay();
                }
            }
        }

        // Insérer le dernier batch
        if (!empty($receptionsBatch)) {
            DB::table('receptions_lait')->insert($receptionsBatch);
        }

        $this->command->info("   ✓ {$totalReceptions} réceptions générées");
    }

    /**
     * Calculer la probabilité de réception selon le jour
     */
    private function getProbabiliteReception($date): int
    {
        return match($date->dayOfWeek) {
            Carbon::SUNDAY => 0,        // Pas de réception le dimanche
            Carbon::SATURDAY => 60,     // Réduite le samedi
            Carbon::MONDAY => 95,       // Forte le lundi (rattrapage weekend)
            default => 85              // Normal les autres jours
        };
    }

    /**
     * Générer une quantité de réception réaliste
     */
    private function generateQuantiteReception($date, $membre): float
    {
        $mois = $date->month;
        
        // Facteur saisonnier
        $facteurSaison = match(true) {
            in_array($mois, [3, 4, 5]) => 1.25,    // Printemps: pic
            in_array($mois, [6, 7, 8]) => 0.85,    // Été: baisse
            in_array($mois, [9, 10, 11]) => 1.10,  // Automne: reprise
            default => 0.75                         // Hiver: minimum
        };

        // Type d'éleveur (basé sur l'historique ou aléatoire)
        $seed = crc32($membre->id_membre); // Seed consistant par membre
        mt_srand($seed);
        $typeEleveur = ['petit', 'moyen', 'grand'][mt_rand(0, 2)];
        mt_srand(); // Reset seed

        $quantiteBase = match($typeEleveur) {
            'petit' => fake()->randomFloat(2, 8, 30),
            'moyen' => fake()->randomFloat(2, 25, 80),
            'grand' => fake()->randomFloat(2, 70, 200),
        };

        $variation = fake()->randomFloat(2, 0.8, 1.2);
        
        return round($quantiteBase * $facteurSaison * $variation, 2);
    }

    /**
     * Générer un matricule de réception
     */
    private function generateMatriculeReception($date): string
    {
        static $counter = 1;
        $year = $date->year;
        return 'REC' . $year . str_pad($counter++, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Mettre à jour tous les stocks
     */
    private function updateStocks($cooperatives): void
    {
        $this->command->info('   📦 Mise à jour des stocks...');
        
        $totalStocks = 0;

        foreach ($cooperatives as $cooperative) {
            $currentDate = $this->dateDebut->copy();
            
            while ($currentDate <= $this->dateFin) {
                try {
                    StockLait::updateDailyStock($cooperative->id_cooperative, $currentDate);
                    $totalStocks++;
                } catch (\Exception $e) {
                    // Log silencieusement les erreurs de doublon
                    if (!str_contains($e->getMessage(), 'unique_cooperative_date_stock')) {
                        $this->command->warn("   Erreur stock: {$e->getMessage()}");
                    }
                }

                $currentDate->addDay();
            }
        }

        $this->command->info("   ✓ {$totalStocks} stocks traités");
    }

    /**
     * Générer les livraisons usine
     */
    private function generateLivraisons($cooperatives): void
    {
        $this->command->info('   🚚 Génération des livraisons...');
        
        $totalLivraisons = 0;

        foreach ($cooperatives as $cooperative) {
            $currentDate = $this->dateDebut->copy();
            
            while ($currentDate <= $this->dateFin) {
                // Livraisons principalement mardi, jeudi, samedi
                if (in_array($currentDate->dayOfWeek, [Carbon::TUESDAY, Carbon::THURSDAY, Carbon::SATURDAY])) {
                    
                    if (fake()->boolean(80)) { // 80% de chance
                        $stock = StockLait::where('id_cooperative', $cooperative->id_cooperative)
                                         ->whereDate('date_stock', $currentDate)
                                         ->first();
                        
                        if ($stock && $stock->quantite_disponible >= 100) { // Minimum 100L
                            $pourcentageLivraison = fake()->randomFloat(2, 0.6, 0.95);
                            $quantite = min(
                                round($stock->quantite_disponible * $pourcentageLivraison, 2),
                                $stock->quantite_disponible
                            );

                            $livraison = LivraisonUsine::factory()
                                ->forCooperative($cooperative)
                                ->onDate($currentDate)
                                ->withQuantite($quantite)
                                ->withProgressiveStatus()
                                ->create();

                            // Mettre à jour le stock
                            try {
                                $stock->livrer($quantite);
                                $totalLivraisons++;
                            } catch (\Exception $e) {
                                $livraison->delete(); // Supprimer la livraison si échec
                            }
                        }
                    }
                }

                $currentDate->addDay();
            }
        }

        $this->command->info("   ✓ {$totalLivraisons} livraisons générées");
    }

    /**
     * Générer les paiements usine
     */
    private function generatePaiementsUsine(): void
    {
        $this->command->info('   💰 Génération des paiements usine...');
        
        $livraisons = LivraisonUsine::whereIn('statut', ['validee', 'payee'])->get();
        $paiements = 0;

        foreach ($livraisons as $livraison) {
            // 85% des livraisons validées ont un paiement
            if (fake()->boolean(85)) {
                PaiementCooperativeUsine::factory()
                    ->forLivraison($livraison)
                    ->withRealisticTiming()
                    ->create();
                
                $paiements++;
            }
        }

        $this->command->info("   ✓ {$paiements} paiements usine générés");
    }

    /**
     * Générer les paiements éleveurs
     */
    private function generatePaiementsEleveurs($cooperatives): void
    {
        $this->command->info('   💳 Génération des paiements éleveurs...');
        
        $totalPaiements = 0;

        foreach ($cooperatives as $cooperative) {
            $currentMonth = $this->dateDebut->copy()->startOfMonth();
            
            while ($currentMonth < $this->dateFin->subDays(10)) { // Éviter le mois en cours
                $membresActifs = $cooperative->membresActifs;
                
                foreach ($membresActifs as $membre) {
                    $debutPeriode = $currentMonth->copy();
                    $finPeriode = $debutPeriode->copy()->endOfMonth();
                    
                    // Calculer les réceptions pour cette période
                    $quantiteTotale = ReceptionLait::where('id_membre', $membre->id_membre)
                                                 ->whereBetween('date_reception', [$debutPeriode, $finPeriode])
                                                 ->sum('quantite_litres');

                    // Créer paiement si quantité > seuil minimum
                    if ($quantiteTotale >= 50) { // Minimum 50L pour paiement
                        PaiementCooperativeEleveur::factory()
                            ->forMembre($membre)
                            ->basedOnReceptions($membre, $debutPeriode, $finPeriode)
                            ->withRealisticTiming()
                            ->create();
                        
                        $totalPaiements++;
                    }
                }

                $currentMonth->addMonth();
            }
        }

        $this->command->info("   ✓ {$totalPaiements} paiements éleveurs générés");
    }

    /**
     * Afficher les statistiques opérationnelles
     */
    private function printOperationalStats(): void
    {
        $this->command->info("\n📊 STATISTIQUES OPÉRATIONNELLES:");
        $this->command->info("================================");

        // Réceptions
        $totalReceptions = ReceptionLait::count();
        $totalLitres = ReceptionLait::sum('quantite_litres');
        $moyenne = $totalReceptions > 0 ? round($totalLitres / $totalReceptions, 2) : 0;
        
        $this->command->info("🥛 Réceptions: {$totalReceptions}");
        $this->command->info("   - Volume total: " . number_format($totalLitres, 2) . " L");
        $this->command->info("   - Moyenne/réception: {$moyenne} L");

        // Stocks
        $stocksCount = StockLait::count();
        $stockTotal = StockLait::sum('quantite_totale');
        $stockDisponible = StockLait::sum('quantite_disponible');
        
        $this->command->info("\n📦 Stocks: {$stocksCount} entrées");
        $this->command->info("   - Stock total cumulé: " . number_format($stockTotal, 2) . " L");
        $this->command->info("   - Actuellement disponible: " . number_format($stockDisponible, 2) . " L");

        // Livraisons
        $livraisons = LivraisonUsine::count();
        $livraisonVolume = LivraisonUsine::sum('quantite_litres');
        $livraisonMontant = LivraisonUsine::sum('montant_total');
        
        $this->command->info("\n🚚 Livraisons: {$livraisons}");
        $this->command->info("   - Volume livré: " . number_format($livraisonVolume, 2) . " L");
        $this->command->info("   - Montant total: " . number_format($livraisonMontant, 2) . " DH");
        $this->command->info("   - Planifiées: " . LivraisonUsine::where('statut', 'planifiee')->count());
        $this->command->info("   - Validées: " . LivraisonUsine::where('statut', 'validee')->count());
        $this->command->info("   - Payées: " . LivraisonUsine::where('statut', 'payee')->count());

        // Paiements usine
        $paiementsUsine = PaiementCooperativeUsine::count();
        $montantUsine = PaiementCooperativeUsine::sum('montant');
        $paiementsUsinePayes = PaiementCooperativeUsine::where('statut', 'paye')->count();
        
        $this->command->info("\n💰 Paiements usine: {$paiementsUsine}");
        $this->command->info("   - Montant total: " . number_format($montantUsine, 2) . " DH");
        $this->command->info("   - Payés: {$paiementsUsinePayes}");
        $this->command->info("   - En attente: " . ($paiementsUsine - $paiementsUsinePayes));

        // Paiements éleveurs
        $paiementsEleveurs = PaiementCooperativeEleveur::count();
        $montantEleveurs = PaiementCooperativeEleveur::sum('montant_total');
        $paiementsEleveursPayes = PaiementCooperativeEleveur::where('statut', 'paye')->count();
        
        $this->command->info("\n💳 Paiements éleveurs: {$paiementsEleveurs}");
        $this->command->info("   - Montant total: " . number_format($montantEleveurs, 2) . " DH");
        $this->command->info("   - Payés: {$paiementsEleveursPayes}");
        $this->command->info("   - Calculés: " . ($paiementsEleveurs - $paiementsEleveursPayes));

        // Ratios
        if ($totalLitres > 0) {
            $tauxLivraison = round(($livraisonVolume / $totalLitres) * 100, 1);
            $this->command->info("\n📈 Taux de livraison: {$tauxLivraison}%");
        }
    }
}