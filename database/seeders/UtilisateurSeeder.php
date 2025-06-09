<?php 
namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Création des utilisateurs du système...');

        // Vérifier s'il y a déjà des utilisateurs
        $existingCount = Utilisateur::count();
        if ($existingCount > 0) {
            $this->command->info("ℹ️  {$existingCount} utilisateurs déjà présents");
            
            // Vérifier si les utilisateurs de test existent déjà
            if (Utilisateur::where('email', 'admin@sgccl.ma')->exists()) {
                $this->command->info("✅ Utilisateurs de test déjà créés, ajout d'utilisateurs supplémentaires...");
                $this->createAdditionalUsers();
                return;
            }
        }

        // Créer les utilisateurs de test avec des identifiants connus
        $this->createTestUsers();

        // Créer des utilisateurs aléatoires supplémentaires
        $this->createRandomUsers();

        $this->afficherStatistiques();
    }

    private function createTestUsers(): void
    {
        $this->command->info('🔑 Création des comptes de test...');

        $testUsers = [
            [
                'matricule' => '0000000001',
                'nom_complet' => 'Administrateur Système',
                'email' => 'admin@sgccl.ma',
                'mot_de_passe' => Hash::make('admin123'),
                'telephone' => '0520123456',
                'role' => 'direction',
                'statut' => 'actif',
            ],
            [
                'matricule' => '1000000001',
                'nom_complet' => 'Ahmed Ben Ali',
                'email' => 'eleveur@test.com',
                'mot_de_passe' => Hash::make('password123'),
                'telephone' => '0612345678',
                'role' => 'éleveur',
                'statut' => 'actif',
            ],
            [
                'matricule' => '2000000001',
                'nom_complet' => 'Fatima Zahra Idrissi',
                'email' => 'gestionnaire@test.com',
                'mot_de_passe' => Hash::make('password123'),
                'telephone' => '0612345679',
                'role' => 'gestionnaire',
                'statut' => 'actif',
            ],
            [
                'matricule' => '3000000001',
                'nom_complet' => 'Mohammed Tazi',
                'email' => 'usva@test.com',
                'mot_de_passe' => Hash::make('password123'),
                'telephone' => '0612345680',
                'role' => 'usva',
                'statut' => 'actif',
            ],
            [
                'matricule' => '4000000001',
                'nom_complet' => 'Aicha Fassi',
                'email' => 'direction@test.com',
                'mot_de_passe' => Hash::make('password123'),
                'telephone' => '0612345681',
                'role' => 'direction',
                'statut' => 'actif',
            ],
        ];

        foreach ($testUsers as $userData) {
            // Éviter les doublons
            if (!Utilisateur::where('email', $userData['email'])->exists()) {
                Utilisateur::create($userData);
                $this->command->info("   ✅ {$userData['nom_complet']} ({$userData['role']})");
            }
        }
    }

    private function createRandomUsers(): void
    {
        $this->command->info('🎲 Création d\'utilisateurs aléatoires...');

        // Gestionnaires (pour assigner aux coopératives)
        Utilisateur::factory(12)->gestionnaire()->actif()->create();
        
        // Autres rôles
        Utilisateur::factory(8)->eleveur()->actif()->create();
        Utilisateur::factory(6)->usva()->actif()->create();
        Utilisateur::factory(4)->direction()->actif()->create();

        // Quelques utilisateurs inactifs
        Utilisateur::factory(3)->gestionnaire()->inactif()->create();
        Utilisateur::factory(2)->eleveur()->inactif()->create();
    }

    private function createAdditionalUsers(): void
    {
        // Ajouter seulement quelques utilisateurs supplémentaires
        Utilisateur::factory(5)->gestionnaire()->actif()->create();
        Utilisateur::factory(3)->eleveur()->actif()->create();
        $this->command->info("✅ 8 utilisateurs supplémentaires créés");
    }

    private function afficherStatistiques(): void
    {
        $total = Utilisateur::count();
        $this->command->info("✅ {$total} utilisateurs au total");

        // Statistiques par rôle
        foreach (['éleveur', 'gestionnaire', 'usva', 'direction'] as $role) {
            $count = Utilisateur::where('role', $role)->count();
            $actifs = Utilisateur::where('role', $role)->where('statut', 'actif')->count();
            $this->command->info("   📊 {$role}: {$count} total ({$actifs} actifs)");
        }

        $this->command->info("\n🔑 COMPTES DE TEST CRÉÉS:");
        $this->command->info("   Admin: admin@sgccl.ma / admin123");
        $this->command->info("   Gestionnaire: gestionnaire@test.com / password123");
        $this->command->info("   USVA: usva@test.com / password123");
        $this->command->info("   Direction: direction@test.com / password123");
    }
}