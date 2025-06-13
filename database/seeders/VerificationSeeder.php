<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class VerificationSeeder extends Seeder
{
    /**
     * Vérification que tout est prêt avant le seeding principal
     */
    public function run(): void
    {
        $this->command->info('🔍 Vérification de l\'environnement...');
        
        // 1. Vérifier les tables
        $this->verifierTables();
        
        // 2. Vérifier les modèles
        $this->verifierModeles();
        
        // 3. Vérifier la connexion DB
        $this->verifierConnexionDB();
        
        $this->command->info('✅ Toutes les vérifications sont passées!');
        $this->command->info('🚀 Vous pouvez maintenant exécuter les seeders principaux.');
    }
    
    private function verifierTables()
    {
        $this->command->info('📋 Vérification des tables...');
        
        $tablesRequises = [
            'utilisateurs',
            'cooperatives', 
            'membres_eleveurs',
            'receptions_lait',
            'stock_lait',
            'livraisons_usine',
            'paiements_cooperative_usine',
            'paiements_cooperative_eleveurs',
            'prix_unitaires'
        ];
        
        $tablesManquantes = [];
        
        foreach ($tablesRequises as $table) {
            if (!Schema::hasTable($table)) {
                $tablesManquantes[] = $table;
            }
        }
        
        if (!empty($tablesManquantes)) {
            $this->command->error('❌ Tables manquantes : ' . implode(', ', $tablesManquantes));
            $this->command->error('Exécutez : php artisan migrate');
            throw new \Exception('Tables manquantes dans la base de données');
        }
        
        $this->command->info('  ✅ Toutes les tables sont présentes');
    }
    
    private function verifierModeles()
    {
        $this->command->info('🏗️  Vérification des modèles...');
        
        $modeles = [
            \App\Models\Utilisateur::class,
            \App\Models\Cooperative::class,
            \App\Models\MembreEleveur::class,
            \App\Models\ReceptionLait::class,
            \App\Models\StockLait::class,
            \App\Models\LivraisonUsine::class,
            \App\Models\PaiementCooperativeUsine::class,
            \App\Models\PaiementCooperativeEleveur::class,
            \App\Models\PrixUnitaire::class,
        ];
        
        foreach ($modeles as $modele) {
            if (!class_exists($modele)) {
                $this->command->error("❌ Modèle manquant : {$modele}");
                throw new \Exception("Modèle {$modele} introuvable");
            }
        }
        
        $this->command->info('  ✅ Tous les modèles sont accessibles');
    }
    
    private function verifierConnexionDB()
    {
        $this->command->info('🔌 Vérification de la connexion base de données...');
        
        try {
            DB::connection()->getPdo();
            $this->command->info('  ✅ Connexion DB active');
        } catch (\Exception $e) {
            $this->command->error('❌ Erreur de connexion DB : ' . $e->getMessage());
            throw $e;
        }
    }
}