<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Utilisateur;
use App\Models\Cooperative;
use App\Models\MembreEleveur;
use App\Models\ReceptionLait;
use App\Models\StockLait;
use App\Models\LivraisonUsine;
use App\Models\PaiementCooperativeUsine;
use App\Models\PaiementCooperativeEleveur;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Début du seeding complet du système laitier...');

        // Seeding complet par défaut
        $this->runFullMode();
    }

    /**
     * Mode complet: tout créer depuis zéro
     */
    private function runFullMode(): void
    {
        $this->command->info('📋 Mode: Seeding complet');

        // 1. Nettoyer d'abord
        $this->call(CleanDataSeeder::class);

        // 2. Créer les données de base
        $this->call(BaseDataSeeder::class);

        // 3. Générer les données opérationnelles
        $this->call(OperationalDataSeeder::class);

        // 4. Ajouter des scénarios de test
        $this->call(TestScenariosSeeder::class);

        $this->command->info('✅ Seeding complet terminé!');
        $this->printFinalStatistics();
    }

    /**
     * Mode nettoyage uniquement
     */
    private function runCleanMode(): void
    {
        $this->command->info('📋 Mode: Nettoyage');
        $this->call(CleanDataSeeder::class);
    }

    /**
     * Mode données de base uniquement
     */
    private function runBaseOnly(): void
    {
        $this->command->info('📋 Mode: Données de base seulement');
        $this->call(BaseDataSeeder::class);
    }

    /**
     * Mode données opérationnelles seulement
     */
    private function runOperationalOnly(): void
    {
        $this->command->info('📋 Mode: Données opérationnelles seulement');
        
        // Nettoyer seulement les données opérationnelles
        $this->command->info('🧹 Nettoyage des données opérationnelles...');
        $cleaner = new CleanDataSeeder();
        $cleaner->cleanOperationalOnly();

        // Générer les nouvelles données opérationnelles
        $this->call(OperationalDataSeeder::class);
    }

    /**
     * Mode test: créer uniquement des scénarios de test
     */
    private function runTestMode(): void
    {
        $this->command->info('📋 Mode: Scénarios de test');
        $this->call(TestScenariosSeeder::class);
    }



    /**
     * Afficher les statistiques finales
     */
    private function printFinalStatistics(): void
    {
        $this->command->info("\n📊 STATISTIQUES FINALES:");
        $this->command->info("======================");
        
        // Utilisateurs
        $this->command->info("👥 Utilisateurs: " . Utilisateur::count());
        $roles = ['direction', 'usva', 'gestionnaire', 'éleveur'];
        foreach ($roles as $role) {
            $count = Utilisateur::where('role', $role)->count();
            $this->command->info("   - " . ucfirst($role) . ": {$count}");
        }
        
        // Coopératives
        $totalCoops = Cooperative::count();
        $coopsActives = Cooperative::where('statut', 'actif')->count();
        $coopsAvecResponsable = Cooperative::whereNotNull('responsable_id')->count();
        
        $this->command->info("\n🏢 Coopératives: {$totalCoops}");
        $this->command->info("   - Actives: {$coopsActives}");
        $this->command->info("   - Avec responsable: {$coopsAvecResponsable}");
        
        // Membres
        $totalMembres = MembreEleveur::count();
        $membresActifs = MembreEleveur::where('statut', 'actif')->count();
        $membresInactifs = MembreEleveur::where('statut', 'inactif')->count();
        $membresSupprimes = MembreEleveur::where('statut', 'suppression')->count();
        
        $this->command->info("\n🐄 Membres éleveurs: {$totalMembres}");
        $this->command->info("   - Actifs: {$membresActifs}");
        $this->command->info("   - Inactifs: {$membresInactifs}");
        $this->command->info("   - Supprimés: {$membresSupprimes}");
        
        // Réceptions
        $totalReceptions = ReceptionLait::count();
        $totalLait = ReceptionLait::sum('quantite_litres') ?? 0;
        $moyenneReception = $totalReceptions > 0 ? round($totalLait / $totalReceptions, 2) : 0;
        
        $this->command->info("\n🥛 Réceptions: {$totalReceptions}");
        $this->command->info("   - Quantité totale: " . number_format($totalLait, 2) . " L");
        $this->command->info("   - Moyenne par réception: {$moyenneReception} L");
        
        // Stocks
        $totalStocks = StockLait::count();
        $this->command->info("\n📦 Stocks: {$totalStocks} entrées");
        
        // Livraisons
        $totalLivraisons = LivraisonUsine::count();
        $totalLivraisionVolume = LivraisonUsine::sum('quantite_litres') ?? 0;
        $livraisonsPlanifiees = LivraisonUsine::where('statut', 'planifiee')->count();
        $livraisonsValidees = LivraisonUsine::where('statut', 'validee')->count();
        $livraisonsPayees = LivraisonUsine::where('statut', 'payee')->count();
        
        $this->command->info("\n🚚 Livraisons usine: {$totalLivraisons}");
        $this->command->info("   - Quantité totale: " . number_format($totalLivraisionVolume, 2) . " L");
        $this->command->info("   - Planifiées: {$livraisonsPlanifiees}");
        $this->command->info("   - Validées: {$livraisonsValidees}");
        $this->command->info("   - Payées: {$livraisonsPayees}");
        
        // Paiements usine
        $totalPaiementsUsine = PaiementCooperativeUsine::count();
        $montantPaiementsUsine = PaiementCooperativeUsine::sum('montant') ?? 0;
        $paiementsUsinePayes = PaiementCooperativeUsine::where('statut', 'paye')->count();
        $paiementsUsineAttente = PaiementCooperativeUsine::where('statut', 'en_attente')->count();
        
        $this->command->info("\n💰 Paiements usine: {$totalPaiementsUsine}");
        $this->command->info("   - Montant total: " . number_format($montantPaiementsUsine, 2) . " DH");
        $this->command->info("   - Payés: {$paiementsUsinePayes}");
        $this->command->info("   - En attente: {$paiementsUsineAttente}");
        
        // Paiements éleveurs
        $totalPaiementsEleveurs = PaiementCooperativeEleveur::count();
        $montantPaiementsEleveurs = PaiementCooperativeEleveur::sum('montant_total') ?? 0;
        $paiementsEleveursPayes = PaiementCooperativeEleveur::where('statut', 'paye')->count();
        $paiementsEleveursCalcules = PaiementCooperativeEleveur::where('statut', 'calcule')->count();
        
        $this->command->info("\n💳 Paiements éleveurs: {$totalPaiementsEleveurs}");
        $this->command->info("   - Montant total: " . number_format($montantPaiementsEleveurs, 2) . " DH");
        $this->command->info("   - Payés: {$paiementsEleveursPayes}");
        $this->command->info("   - Calculés: {$paiementsEleveursCalcules}");
        
        $this->command->info("\n🎉 Base de données prête pour les tests!");
    }
}