<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du processus de seeding complet...');
        $this->command->info('⚠️  ATTENTION: Ce processus va créer des milliers d\'enregistrements et peut prendre plusieurs minutes.');
        
        // Confirmation avant exécution
        if (!$this->command->confirm('Voulez-vous continuer ?', true)) {
            $this->command->info('❌ Processus annulé par l\'utilisateur.');
            return;
        }

        $startTime = microtime(true);

        // ÉTAPE 1: Seeders des tables de base (migrations 1-2)
        $this->command->info("\n🏗️  ÉTAPE 1: Tables de base (Utilisateurs & Coopératives)");
        $this->command->info('ℹ️  Assurez-vous d\'avoir exécuté UtilisateurSeeder et CooperativeSeeder avant de continuer.');
        
        if (!$this->command->confirm('Les seeders des utilisateurs et coopératives ont-ils été exécutés ?', false)) {
            $this->command->error('❌ Veuillez d\'abord exécuter:');
            $this->command->error('   php artisan db:seed --class=UtilisateurSeeder');
            $this->command->error('   php artisan db:seed --class=CooperativeSeeder');
            return;
        }

        // ÉTAPE 2: Membres Éleveurs (Migration 3)
        $this->command->info("\n👥 ÉTAPE 2: Création des membres éleveurs...");
        $this->call(MembreEleveurSeeder::class);

        // ÉTAPE 3: Réceptions de Lait (Migration 4)
        $this->command->info("\n🥛 ÉTAPE 3: Génération de l'historique des réceptions de lait...");
        $this->call(ReceptionLaitSeeder::class);

        // ÉTAPE 4: Stocks de Lait (Migration 5)
        $this->command->info("\n📦 ÉTAPE 4: Consolidation des stocks quotidiens...");
        $this->call(StockLaitSeeder::class);

        // ÉTAPE 5: Livraisons Usine (Migration 6)
        $this->command->info("\n🚛 ÉTAPE 5: Génération des livraisons à l'usine...");
        $this->call(LivraisonUsineSeeder::class);

        // ÉTAPE 6: Paiements Coopérative-Usine (Migration 7)
        $this->command->info("\n💰 ÉTAPE 6: Création des paiements coopérative-usine...");
        $this->call(PaiementCooperativeUsineSeeder::class);

        // Statistiques finales
        $this->afficherStatistiquesFinales($startTime);
        
        $this->command->info("\n✅ PROCESSUS TERMINÉ AVEC SUCCÈS!");
        $this->command->info("🎉 Votre base de données est maintenant peuplée avec des données réalistes.");
        $this->command->info("📊 Vous pouvez maintenant tester votre application avec un historique complet de 6 mois.");
    }

    /**
     * Afficher les statistiques finales de création
     */
    private function afficherStatistiquesFinales(float $startTime): void
    {
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->command->info("\n📊 RAPPORT FINAL:");
        $this->command->info("⏱️  Temps d'exécution: {$duration} secondes");
        
        // Compter les enregistrements créés
        $stats = [
            'Membres Éleveurs' => \App\Models\MembreEleveur::count(),
            'Réceptions de Lait' => \App\Models\ReceptionLait::count(),
            'Stocks de Lait' => \App\Models\StockLait::count(),
            'Livraisons Usine' => \App\Models\LivraisonUsine::count(),
            'Paiements Usine' => \App\Models\PaiementCooperativeUsine::count(),
        ];

        $this->command->info("\n📈 Enregistrements créés:");
        $totalRecords = 0;
        foreach ($stats as $table => $count) {
            $this->command->info("   - {$table}: " . number_format($count));
            $totalRecords += $count;
        }
        $this->command->info("   TOTAL: " . number_format($totalRecords) . " enregistrements");

        // Calculer les volumes financiers
        $volumeFinancier = $this->calculerVolumeFinancier();
        if ($volumeFinancier) {
            $this->command->info("\n💵 Volumes financiers simulés:");
            $this->command->info("   - Chiffre d'affaires total: " . number_format($volumeFinancier['ca_total'], 2) . " DH");
            $this->command->info("   - Volume de lait total: " . number_format($volumeFinancier['volume_total'], 2) . " litres");
            $this->command->info("   - Prix moyen: " . number_format($volumeFinancier['prix_moyen'], 2) . " DH/litre");
        }

        // Période couverte
        $periode = $this->calculerPeriodeCouverte();
        if ($periode) {
            $this->command->info("\n📅 Période d'historique:");
            $this->command->info("   - Du: {$periode['debut']}");
            $this->command->info("   - Au: {$periode['fin']}");
            $this->command->info("   - Durée: {$periode['duree']} jours");
        }
    }

    /**
     * Calculer le volume financier total
     */
    private function calculerVolumeFinancier(): ?array
    {
        try {
            $stats = \App\Models\LivraisonUsine::selectRaw('
                SUM(montant_total) as ca_total,
                SUM(quantite_litres) as volume_total,
                AVG(prix_unitaire) as prix_moyen
            ')->first();

            if ($stats && $stats->ca_total > 0) {
                return [
                    'ca_total' => $stats->ca_total,
                    'volume_total' => $stats->volume_total,
                    'prix_moyen' => $stats->prix_moyen,
                ];
            }
        } catch (\Exception $e) {
            // Si les modèles n'existent pas encore, ignorer silencieusement
        }

        return null;
    }

    /**
     * Calculer la période couverte par les données
     */
    private function calculerPeriodeCouverte(): ?array
    {
        try {
            $periode = \App\Models\ReceptionLait::selectRaw('
                MIN(date_reception) as debut,
                MAX(date_reception) as fin,
                DATEDIFF(MAX(date_reception), MIN(date_reception)) + 1 as duree
            ')->first();

            if ($periode && $periode->debut) {
                return [
                    'debut' => \Carbon\Carbon::parse($periode->debut)->format('d/m/Y'),
                    'fin' => \Carbon\Carbon::parse($periode->fin)->format('d/m/Y'),
                    'duree' => $periode->duree,
                ];
            }
        } catch (\Exception $e) {
            // Si les modèles n'existent pas encore, ignorer silencieusement
        }

        return null;
    }
}

/**
 * SEEDER SPÉCIALISÉ POUR LES MIGRATIONS 3-7 UNIQUEMENT
 * 
 * Si vous voulez exécuter seulement les seeders des migrations 3 à 7:
 * php artisan db:seed --class=LaiterieSeeders
 */
class LaiterieSeeders extends Seeder
{
    /**
     * Run the database seeds for migrations 3-7 only.
     */
    public function run(): void
    {
        $this->command->info('🥛 Exécution des seeders spécifiques à la laiterie (migrations 3-7)...');
        
        // Vérifications préalables
        $cooperativesCount = \App\Models\Cooperative::count();
        if ($cooperativesCount === 0) {
            $this->command->error('❌ Aucune coopérative trouvée. Veuillez d\'abord exécuter CooperativeSeeder.');
            return;
        }

        $this->command->info("✅ {$cooperativesCount} coopératives trouvées. Démarrage du processus...");

        $startTime = microtime(true);

        // Exécution séquentielle des seeders
        $this->command->info("\n1️⃣  Création des membres éleveurs...");
        $this->call(MembreEleveurSeeder::class);

        $this->command->info("\n2️⃣  Génération des réceptions de lait...");
        $this->call(ReceptionLaitSeeder::class);

        $this->command->info("\n3️⃣  Consolidation des stocks...");
        $this->call(StockLaitSeeder::class);

        $this->command->info("\n4️⃣  Création des livraisons...");
        $this->call(LivraisonUsineSeeder::class);

        $this->command->info("\n5️⃣  Génération des paiements...");
        $this->call(PaiementCooperativeUsineSeeder::class);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->command->info("\n✅ Seeders de laiterie terminés en {$duration} secondes!");
    }
}

/**
 * SEEDER DE DÉVELOPPEMENT - DONNÉES MINIMALES POUR LES TESTS
 * 
 * Pour créer rapidement un jeu de données minimal pour le développement:
 * php artisan db:seed --class=DevLaiterieSeeder
 */
class DevLaiterieSeeder extends Seeder
{
    /**
     * Run a minimal dataset for development.
     */
    public function run(): void
    {
        $this->command->info('🔧 Création d\'un jeu de données minimal pour le développement...');

        // Vérifier les prérequis
        $cooperativesCount = \App\Models\Cooperative::count();
        if ($cooperativesCount === 0) {
            $this->command->error('❌ Aucune coopérative trouvée.');
            return;
        }

        // Limiter les données pour le développement
        $cooperatives = \App\Models\Cooperative::limit(2)->get(); // Seulement 2 coopératives

        foreach ($cooperatives as $cooperative) {
            $this->command->info("Traitement de: {$cooperative->nom_cooperative}");

            // 10-15 membres par coopérative seulement
            \App\Models\MembreEleveur::factory()
                ->count(rand(10, 15))
                ->actif()
                ->create(['id_cooperative' => $cooperative->id_cooperative]);
        }

        // Réceptions sur 1 mois seulement
        $membresActifs = \App\Models\MembreEleveur::where('statut', 'actif')->get();
        $dateDebut = \Carbon\Carbon::now()->subMonth();
        $dateFin = \Carbon\Carbon::now();

        $this->command->info("Création des réceptions pour {$membresActifs->count()} membres sur 1 mois...");

        foreach ($membresActifs as $membre) {
            $dateActuelle = $dateDebut->copy();
            while ($dateActuelle->lte($dateFin)) {
                if (rand(1, 100) <= 70) { // 70% de chance de livrer
                    \App\Models\ReceptionLait::factory()
                        ->pourMembre($membre->id_membre, $membre->id_cooperative)
                        ->pourDate($dateActuelle->format('Y-m-d'))
                        ->create();
                }
                $dateActuelle->addDay();
            }
        }

        // Générer les stocks, livraisons et paiements
        $this->call(StockLaitSeeder::class);
        $this->call(LivraisonUsineSeeder::class);
        $this->call(PaiementCooperativeUsineSeeder::class);

        $this->command->info("✅ Jeu de données de développement créé avec succès!");
        $this->command->info("📊 Données créées: " . 
            \App\Models\MembreEleveur::count() . " membres, " . 
            \App\Models\ReceptionLait::count() . " réceptions");
    }
}