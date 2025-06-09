<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder pour nettoyer et réinitialiser les données
 */
class CleanDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧹 Nettoyage de la base de données...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Nettoyer toutes les tables dans l'ordre inverse des dépendances
        $this->cleanOperationalData();
        $this->cleanBaseData();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Base de données nettoyée avec succès!');
    }

    /**
     * Nettoyer seulement les données opérationnelles
     */
    public function cleanOperationalOnly(): void
    {
        $this->command->info('🧹 Nettoyage des données opérationnelles seulement...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $this->cleanOperationalData();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Données opérationnelles nettoyées!');
    }

    /**
     * Nettoyer les données opérationnelles
     */
    private function cleanOperationalData(): void
    {
        $operationalTables = [
            'paiements_cooperative_eleveurs',
            'paiements_cooperative_usine',
            'livraisons_usine',
            'stock_lait',
            'receptions_lait',
        ];

        foreach ($operationalTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->command->info("   🗑️ Table '{$table}' vidée ({$count} enregistrements supprimés)");
            }
        }
    }

    /**
     * Nettoyer les données de base
     */
    private function cleanBaseData(): void
    {
        $baseTables = [
            'membres_eleveurs',
            'cooperatives',
            'utilisateurs',
        ];

        foreach ($baseTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->command->info("   🗑️ Table '{$table}' vidée ({$count} enregistrements supprimés)");
            }
        }
    }

    /**
     * Nettoyer une table spécifique
     */
    public function cleanTable(string $tableName): void
    {
        if (Schema::hasTable($tableName)) {
            $count = DB::table($tableName)->count();
            DB::table($tableName)->truncate();
            $this->command->info("Table '{$tableName}' vidée ({$count} enregistrements supprimés)");
        } else {
            $this->command->warn("Table '{$tableName}' n'existe pas");
        }
    }

    /**
     * Nettoyer les données anciennes (garder X mois)
     */
    public function cleanOldData(int $moisAGarder = 6): void
    {
        $this->command->info("🧹 Nettoyage des données anciennes (gardant {$moisAGarder} mois)...");

        $dateLimit = now()->subMonths($moisAGarder);

        // Nettoyer les réceptions anciennes
        $receptionsDeleted = DB::table('receptions_lait')
            ->where('date_reception', '<', $dateLimit)
            ->delete();

        if ($receptionsDeleted > 0) {
            $this->command->info("   🗑️ {$receptionsDeleted} réceptions anciennes supprimées");
        }

        // Nettoyer les stocks anciens
        $stocksDeleted = DB::table('stock_lait')
            ->where('date_stock', '<', $dateLimit)
            ->delete();

        if ($stocksDeleted > 0) {
            $this->command->info("   🗑️ {$stocksDeleted} stocks anciens supprimés");
        }

        // Nettoyer les livraisons anciennes
        $livraisonsDeleted = DB::table('livraisons_usine')
            ->where('date_livraison', '<', $dateLimit)
            ->delete();

        if ($livraisonsDeleted > 0) {
            $this->command->info("   🗑️ {$livraisonsDeleted} livraisons anciennes supprimées");
        }

        // Nettoyer les paiements anciens
        $paiementsUsineDeleted = DB::table('paiements_cooperative_usine')
            ->where('date_paiement', '<', $dateLimit)
            ->delete();

        $paiementsEleveursDeleted = DB::table('paiements_cooperative_eleveurs')
            ->where('periode_fin', '<', $dateLimit)
            ->delete();

        if ($paiementsUsineDeleted > 0 || $paiementsEleveursDeleted > 0) {
            $total = $paiementsUsineDeleted + $paiementsEleveursDeleted;
            $this->command->info("   🗑️ {$total} paiements anciens supprimés");
        }

        $this->command->info('✅ Nettoyage des données anciennes terminé!');
    }

    /**
     * Réinitialiser les compteurs auto-increment
     */
    public function resetAutoIncrement(): void
    {
        $this->command->info('🔄 Réinitialisation des compteurs...');

        $tables = [
            'utilisateurs',
            'cooperatives',
            'membres_eleveurs',
            'receptions_lait',
            'stock_lait',
            'livraisons_usine',
            'paiements_cooperative_usine',
            'paiements_cooperative_eleveurs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
            }
        }

        $this->command->info('✅ Compteurs réinitialisés!');
    }

    /**
     * Afficher les statistiques avant nettoyage
     */
    public function showStatistics(): void
    {
        $this->command->info('📊 STATISTIQUES ACTUELLES:');
        $this->command->info('=========================');

        $tables = [
            'utilisateurs' => 'Utilisateurs',
            'cooperatives' => 'Coopératives',
            'membres_eleveurs' => 'Membres éleveurs',
            'receptions_lait' => 'Réceptions',
            'stock_lait' => 'Stocks',
            'livraisons_usine' => 'Livraisons',
            'paiements_cooperative_usine' => 'Paiements usine',
            'paiements_cooperative_eleveurs' => 'Paiements éleveurs',
        ];

        foreach ($tables as $table => $label) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->command->info("{$label}: {$count}");
            }
        }
    }

    /**
     * Optimiser les tables après nettoyage
     */
    public function optimizeTables(): void
    {
        $this->command->info('⚡ Optimisation des tables...');

        $tables = [
            'utilisateurs',
            'cooperatives', 
            'membres_eleveurs',
            'receptions_lait',
            'stock_lait',
            'livraisons_usine',
            'paiements_cooperative_usine',
            'paiements_cooperative_eleveurs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("OPTIMIZE TABLE {$table}");
            }
        }

        $this->command->info('✅ Tables optimisées!');
    }

    /**
     * Nettoyer les données de test spécifiques
     */
    public function cleanTestData(): void
    {
        $this->command->info('🧪 Nettoyage des données de test...');

        // Supprimer les coopératives de test
        $testCooperatives = DB::table('cooperatives')
            ->where('nom_cooperative', 'LIKE', '%Test%')
            ->orWhere('nom_cooperative', 'LIKE', '%Difficultés%')
            ->orWhere('nom_cooperative', 'LIKE', '%Excellence%')
            ->orWhere('nom_cooperative', 'LIKE', '%Retards%')
            ->orWhere('nom_cooperative', 'LIKE', '%Industrielle%')
            ->orWhere('nom_cooperative', 'LIKE', '%Saisonnière%')
            ->pluck('id_cooperative');

        if ($testCooperatives->isNotEmpty()) {
            // Supprimer tous les enregistrements liés
            DB::table('paiements_cooperative_eleveurs')
                ->whereIn('id_cooperative', $testCooperatives)
                ->delete();
            
            DB::table('paiements_cooperative_usine')
                ->whereIn('id_cooperative', $testCooperatives)
                ->delete();
            
            DB::table('livraisons_usine')
                ->whereIn('id_cooperative', $testCooperatives)
                ->delete();
            
            DB::table('stock_lait')
                ->whereIn('id_cooperative', $testCooperatives)
                ->delete();
            
            DB::table('receptions_lait')
                ->whereIn('id_cooperative', $testCooperatives)
                ->delete();
            
            DB::table('membres_eleveurs')
                ->whereIn('id_cooperative', $testCooperatives)
                ->delete();
            
            $cooperativesDeleted = DB::table('cooperatives')
                ->whereIn('id_cooperative', $testCooperatives)
                ->delete();

            $this->command->info("   🗑️ {$cooperativesDeleted} coopératives de test supprimées");
        }

        // Supprimer les utilisateurs de test
        $usersDeleted = DB::table('utilisateurs')
            ->where('nom_complet', 'LIKE', '%Test%')
            ->orWhere('email', 'LIKE', '%test%')
            ->delete();

        if ($usersDeleted > 0) {
            $this->command->info("   🗑️ {$usersDeleted} utilisateurs de test supprimés");
        }

        $this->command->info('✅ Données de test nettoyées!');
    }
}