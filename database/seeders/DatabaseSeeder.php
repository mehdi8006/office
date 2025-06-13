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
        $this->command->info('🌱 Démarrage du seeding complet de la base de données...');
        
        // 0. Vérification optionnelle (décommenter si problèmes)
        // $this->call(VerificationSeeder::class);
        
        // 1. Données de base (utilisateurs, coopératives, membres, prix)
        $this->command->info("\n📋 === DONNÉES DE BASE ===");
        $this->call(BaseDataSeeder::class);
        
        // 2. Données des mois 5 et 6 (mai et juin)
        $this->command->info("\n📅 === DONNÉES HISTORIQUES ===");
        $this->call(Mois5Et6Seeder::class);
        
        // 3. Autres seeders existants (à décommenter si vous en avez)
        // $this->call(AutreSeeder::class);
        
        $this->command->info("\n🎉 Seeding complet terminé avec succès!");
        $this->afficherResume();
    }
    
    /**
     * Affiche un résumé des données créées
     */
    private function afficherResume()
    {
        $this->command->info("\n📊 === RÉSUMÉ DES DONNÉES CRÉÉES ===");
        
        try {
            $stats = [
                'Utilisateurs' => \App\Models\Utilisateur::count(),
                'Coopératives' => \App\Models\Cooperative::count(),
                'Membres éleveurs' => \App\Models\MembreEleveur::count(),
                'Réceptions lait' => \App\Models\ReceptionLait::count(),
                'Stocks quotidiens' => \App\Models\StockLait::count(),
                'Livraisons usine' => \App\Models\LivraisonUsine::count(),
                'Paiements usine' => \App\Models\PaiementCooperativeUsine::count(),
                'Paiements éleveurs' => \App\Models\PaiementCooperativeEleveur::count(),
            ];
            
            foreach ($stats as $type => $count) {
                $this->command->line("  • {$type}: {$count}");
            }
            
            // Statistiques des quantités
            $totalLait = \App\Models\ReceptionLait::sum('quantite_litres');
            $totalLivre = \App\Models\LivraisonUsine::sum('quantite_litres');
            $totalPaiementsUsine = \App\Models\PaiementCooperativeUsine::sum('montant');
            
            $this->command->info("\n💰 === STATISTIQUES QUANTITÉS ===");
            $this->command->line("  • Total lait collecté: " . number_format($totalLait, 2) . " L");
            $this->command->line("  • Total lait livré: " . number_format($totalLivre, 2) . " L");
            $this->command->line("  • Total paiements usine: " . number_format($totalPaiementsUsine, 2) . " DH");
            
        } catch (\Exception $e) {
            $this->command->error("Erreur lors de l'affichage du résumé: " . $e->getMessage());
        }
    }
}