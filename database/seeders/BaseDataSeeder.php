<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use App\Models\Cooperative;
use App\Models\MembreEleveur;

/**
 * Seeder pour les données de base du système
 * (Utilisateurs, Coopératives, Membres)
 */
class BaseDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔧 Création des données de base...');

        // 1. Utilisateurs système
        $this->createSystemUsers();

        // 2. Gestionnaires et autres utilisateurs
        $gestionnaires = $this->createGestionnaires();
        $this->createOtherUsers();

        // 3. Coopératives
        $cooperatives = $this->createCooperatives($gestionnaires);

        // 4. Membres éleveurs
        $this->createMembres($cooperatives);

        $this->command->info('✅ Données de base créées avec succès!');
    }

    /**
     * Créer les utilisateurs système essentiels
     */
    private function createSystemUsers(): void
    {
        $this->command->info('   👤 Création des utilisateurs système...');

        // Super admin
        Utilisateur::factory()->direction()->create([
            'nom_complet' => 'Super Administrateur',
            'email' => 'admin@laiterie.ma',
            'matricule' => '0000000001',
            'statut' => 'actif'
        ]);

        // Agent USVA principal
        Utilisateur::factory()->usva()->create([
            'nom_complet' => 'Mohamed El Alami',
            'email' => 'usva.principal@laiterie.ma',
            'matricule' => '0000000002',
            'statut' => 'actif'
        ]);

        // Agent USVA régional
        Utilisateur::factory()->usva()->create([
            'nom_complet' => 'Fatima Benali',
            'email' => 'usva.regional@laiterie.ma',
            'matricule' => '0000000003',
            'statut' => 'actif'
        ]);

        // Directeur
        Utilisateur::factory()->direction()->create([
            'nom_complet' => 'Ahmed Benjelloun',
            'email' => 'directeur@laiterie.ma',
            'matricule' => '0000000004',
            'statut' => 'actif'
        ]);

        $this->command->info('   ✓ 4 utilisateurs système créés');
    }

    /**
     * Créer les gestionnaires de coopératives
     */
    private function createGestionnaires(): \Illuminate\Database\Eloquent\Collection
    {
        $this->command->info('   👥 Création des gestionnaires...');

        $gestionnaires = Utilisateur::factory()
            ->gestionnaire()
            ->count(10)
            ->create();

        $this->command->info("   ✓ {$gestionnaires->count()} gestionnaires créés");
        return $gestionnaires;
    }

    /**
     * Créer les autres utilisateurs
     */
    private function createOtherUsers(): void
    {
        $this->command->info('   👥 Création des autres utilisateurs...');

        // Personnel de direction
        $direction = Utilisateur::factory()
            ->direction()
            ->count(3)
            ->create();

        // Agents USVA supplémentaires
        $usva = Utilisateur::factory()
            ->usva()
            ->count(2)
            ->create();

        // Utilisateurs éleveurs (pour tests)
        $eleveurs = Utilisateur::factory()
            ->eleveur()
            ->count(5)
            ->create();

        $total = $direction->count() + $usva->count() + $eleveurs->count();
        $this->command->info("   ✓ {$total} autres utilisateurs créés");
    }

    /**
     * Créer les coopératives
     */
    private function createCooperatives($gestionnaires): \Illuminate\Support\Collection
    {
        $this->command->info('   🏢 Création des coopératives...');

        $cooperatives = collect();

        // Coopératives avec gestionnaires assignés
        foreach ($gestionnaires as $gestionnaire) {
            $regions = [
                'Casablanca-Settat', 'Rabat-Salé-Kénitra', 'Fès-Meknès', 
                'Marrakech-Safi', 'Souss-Massa', 'Tanger-Tétouan-Al Hoceïma'
            ];

            $cooperative = Cooperative::factory()
                ->actif()
                ->inRegion(fake()->randomElement($regions))
                ->create([
                    'responsable_id' => $gestionnaire->id_utilisateur,
                ]);
            
            $cooperatives->push($cooperative);
        }

        // Quelques coopératives sans responsable assigné
        $cooperativesSansResponsable = Cooperative::factory()
            ->count(3)
            ->state(['statut' => fake()->randomElement(['actif', 'inactif'])])
            ->create();

        $cooperatives = $cooperatives->merge($cooperativesSansResponsable);

        $this->command->info("   ✓ {$cooperatives->count()} coopératives créées");
        return $cooperatives;
    }

    /**
     * Créer les membres éleveurs
     */
    private function createMembres($cooperatives): void
    {
        $this->command->info('   🐄 Création des membres éleveurs...');

        $totalMembres = 0;

        foreach ($cooperatives as $cooperative) {
            // Nombre variable selon la taille de la coopérative
            $taille = fake()->randomElement(['petite', 'moyenne', 'grande']);
            
            $nombreMembres = match($taille) {
                'petite' => fake()->numberBetween(5, 12),
                'moyenne' => fake()->numberBetween(12, 25),
                'grande' => fake()->numberBetween(25, 40),
            };

            // Membres actifs (majorité)
            $membresActifs = MembreEleveur::factory()
                ->count($nombreMembres)
                ->actif()
                ->forCooperative($cooperative)
                ->create();

            // Membres avec différents statuts et historiques
            $membresAnciens = MembreEleveur::factory()
                ->count(fake()->numberBetween(2, 5))
                ->ancien()
                ->actif()
                ->forCooperative($cooperative)
                ->create();

            // Quelques membres inactifs
            $membresInactifs = MembreEleveur::factory()
                ->count(fake()->numberBetween(1, 3))
                ->inactif()
                ->forCooperative($cooperative)
                ->create();

            // Membres supprimés avec raisons
            $membresSupprimes = MembreEleveur::factory()
                ->count(fake()->numberBetween(0, 2))
                ->supprime()
                ->forCooperative($cooperative)
                ->create();

            $totalMembres += $membresActifs->count() + $membresAnciens->count() + 
                           $membresInactifs->count() + $membresSupprimes->count();
        }

        $this->command->info("   ✓ {$totalMembres} membres créés");
        
        // Statistiques détaillées
        $this->command->info("     - Actifs: " . MembreEleveur::where('statut', 'actif')->count());
        $this->command->info("     - Inactifs: " . MembreEleveur::where('statut', 'inactif')->count());
        $this->command->info("     - Supprimés: " . MembreEleveur::where('statut', 'suppression')->count());
    }
}