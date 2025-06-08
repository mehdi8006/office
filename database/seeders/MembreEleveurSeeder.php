<?php

namespace Database\Seeders;

use App\Models\Cooperative;
use App\Models\MembreEleveur;
use Illuminate\Database\Seeder;

class MembreEleveurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer toutes les coopératives existantes
        $cooperatives = Cooperative::all();

        if ($cooperatives->isEmpty()) {
            $this->command->warn('Aucune coopérative trouvée. Veuillez d\'abord exécuter CooperativeSeeder.');
            return;
        }

        $this->command->info('Création des membres éleveurs...');

        foreach ($cooperatives as $cooperative) {
            // Nombre variable de membres par coopérative (15 à 80 membres)
            $nombreMembres = rand(15, 80);
            
            $this->command->info("Création de {$nombreMembres} membres pour la coopérative: {$cooperative->nom_cooperative}");

            // Distribution des statuts (réaliste)
            $membresActifs = (int) ($nombreMembres * 0.85);     // 85% actifs
            $membresInactifs = (int) ($nombreMembres * 0.10);   // 10% inactifs
            $membresSuppression = $nombreMembres - $membresActifs - $membresInactifs; // Le reste en suppression

            // Créer les membres actifs
            if ($membresActifs > 0) {
                MembreEleveur::factory()
                    ->count($membresActifs)
                    ->actif()
                    ->create([
                        'id_cooperative' => $cooperative->id_cooperative,
                    ]);
            }

            // Créer les membres inactifs
            if ($membresInactifs > 0) {
                MembreEleveur::factory()
                    ->count($membresInactifs)
                    ->inactif()
                    ->create([
                        'id_cooperative' => $cooperative->id_cooperative,
                    ]);
            }

            // Créer les membres en suppression
            if ($membresSuppression > 0) {
                MembreEleveur::factory()
                    ->count($membresSuppression)
                    ->enSuppression()
                    ->create([
                        'id_cooperative' => $cooperative->id_cooperative,
                    ]);
            }
        }

        $totalMembres = MembreEleveur::count();
        $membresActifs = MembreEleveur::where('statut', 'actif')->count();
        $membresInactifs = MembreEleveur::where('statut', 'inactif')->count();
        $membresSuppression = MembreEleveur::where('statut', 'suppression')->count();

        $this->command->info("✅ {$totalMembres} membres éleveurs créés:");
        $this->command->info("   - Actifs: {$membresActifs}");
        $this->command->info("   - Inactifs: {$membresInactifs}");
        $this->command->info("   - En suppression: {$membresSuppression}");
        
        // Afficher la répartition par coopérative
        $this->command->info("\n📊 Répartition par coopérative:");
        foreach ($cooperatives as $cooperative) {
            $count = MembreEleveur::where('id_cooperative', $cooperative->id_cooperative)->count();
            $this->command->info("   - {$cooperative->nom_cooperative}: {$count} membres");
        }
    }
}