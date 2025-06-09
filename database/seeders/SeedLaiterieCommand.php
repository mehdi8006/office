<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\BaseDataSeeder;
use Database\Seeders\OperationalDataSeeder;
use Database\Seeders\TestScenariosSeeder;
use Database\Seeders\CleanDataSeeder;

class SeedLaiterieCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laiterie:seed 
                           {--mode=full : Mode de seeding (full, base, operational, test, clean)}
                           {--months=6 : Nombre de mois pour les données opérationnelles}
                           {--clean : Nettoyer avant de seeder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeder spécialisé pour le système laitier avec différents modes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mode = $this->option('mode');
        $months = (int) $this->option('months');
        $clean = $this->option('clean');

        $this->info('🚀 Seeding du système laitier...');
        $this->info("📋 Mode: {$mode}");

        // Nettoyage préalable si demandé
        if ($clean) {
            $this->cleanDatabase();
        }

        // Exécution selon le mode
        match($mode) {
            'full' => $this->seedFull($months),
            'base' => $this->seedBase(),
            'operational' => $this->seedOperational($months),
            'test' => $this->seedTest(),
            'clean' => $this->cleanDatabase(),
            default => $this->error("Mode '{$mode}' non reconnu. Modes disponibles: full, base, operational, test, clean")
        };
    }

    /**
     * Seeding complet
     */
    private function seedFull(int $months): void
    {
        $this->info('🔄 Seeding complet...');
        
        $this->cleanDatabase();
        $this->seedBase();
        $this->seedOperational($months);
        $this->seedTest();
        
        $this->info('✅ Seeding complet terminé!');
        $this->showStatistics();
    }

    /**
     * Seeding des données de base
     */
    private function seedBase(): void
    {
        $this->info('👥 Création des données de base...');
        
        $seeder = new BaseDataSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('✅ Données de base créées!');
    }

    /**
     * Seeding des données opérationnelles
     */
    private function seedOperational(int $months): void
    {
        $this->info("🔄 Génération des données opérationnelles ({$months} mois)...");
        
        $seeder = new OperationalDataSeeder();
        if ($months !== 6) {
            $seeder->setPeriode($months);
        }
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('✅ Données opérationnelles générées!');
    }

    /**
     * Seeding des scénarios de test
     */
    private function seedTest(): void
    {
        $this->info('🧪 Création des scénarios de test...');
        
        $seeder = new TestScenariosSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('✅ Scénarios de test créés!');
    }

    /**
     * Nettoyage de la base
     */
    private function cleanDatabase(): void
    {
        $this->info('🧹 Nettoyage de la base de données...');
        
        $seeder = new CleanDataSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('✅ Base de données nettoyée!');
    }

    /**
     * Afficher les statistiques
     */
    private function showStatistics(): void
    {
        $this->info("\n📊 STATISTIQUES FINALES:");
        $this->info("======================");
        
        // Importer les modèles
        $stats = [
            'Utilisateurs' => \App\Models\Utilisateur::count(),
            'Coopératives' => \App\Models\Cooperative::count(),
            'Membres éleveurs' => \App\Models\MembreEleveur::count(),
            'Réceptions' => \App\Models\ReceptionLait::count(),
            'Stocks' => \App\Models\StockLait::count(),
            'Livraisons' => \App\Models\LivraisonUsine::count(),
            'Paiements usine' => \App\Models\PaiementCooperativeUsine::count(),
            'Paiements éleveurs' => \App\Models\PaiementCooperativeEleveur::count(),
        ];

        foreach ($stats as $label => $count) {
            $this->info("{$label}: {$count}");
        }

        // Volumes
        $totalLait = \App\Models\ReceptionLait::sum('quantite_litres') ?? 0;
        $totalLivraisons = \App\Models\LivraisonUsine::sum('quantite_litres') ?? 0;
        
        $this->info("\n📈 VOLUMES:");
        $this->info("Lait collecté: " . number_format($totalLait, 2) . " L");
        $this->info("Lait livré: " . number_format($totalLivraisons, 2) . " L");
        
        if ($totalLait > 0) {
            $tauxLivraison = round(($totalLivraisons / $totalLait) * 100, 1);
            $this->info("Taux de livraison: {$tauxLivraison}%");
        }

        $this->info("\n🎉 Base de données prête!");
    }
}