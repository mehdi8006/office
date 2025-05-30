<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific test users with known credentials
        $this->createTestUsers();

        // Create random users for each role
        $this->createRandomUsers();
    }

    /**
     * Create specific test users with known credentials for testing.
     */
    private function createTestUsers(): void
    {
        $testUsers = [
            [
                'matricule' => '1000000001',
                'nom_complet' => 'أحمد محمد الإدريسي',
                'email' => 'eleveur@test.com',
                'mot_de_passe' => Hash::make('password123'),
                'telephone' => '0612345678',
                'role' => 'éleveur',
                'statut' => 'actif',
            ],
            [
                'matricule' => '2000000001',
                'nom_complet' => 'فاطمة الزهراء بنعلي',
                'email' => 'gestionnaire@test.com',
                'mot_de_passe' => Hash::make('password123'),
                'telephone' => '0612345679',
                'role' => 'gestionnaire',
                'statut' => 'actif',
            ],
            [
                'matricule' => '3000000001',
                'nom_complet' => 'محمد عبد الرحمن التازي',
                'email' => 'usva@test.com',
                'mot_de_passe' => Hash::make('password123'),
                'telephone' => '0612345680',
                'role' => 'usva',
                'statut' => 'actif',
            ],
            [
                'matricule' => '4000000001',
                'nom_complet' => 'عائشة خديجة الفاسي',
                'email' => 'direction@test.com',
                'mot_de_passe' => Hash::make('password123'),
                'telephone' => '0612345681',
                'role' => 'direction',
                'statut' => 'actif',
            ],
            [
                'matricule' => '1000000002',
                'nom_complet' => 'عبد الله يوسف الغربي',
                'email' => 'admin@sgccl.ma',
                'mot_de_passe' => Hash::make('admin123'),
                'telephone' => '0612345682',
                'role' => 'direction',
                'statut' => 'actif',
            ],
        ];

        foreach ($testUsers as $userData) {
            Utilisateur::create($userData);
        }

        $this->command->info('✅ Test users created successfully!');
        $this->command->info('📝 Test Credentials:');
        $this->command->info('   Éleveur: 1000000001 / password123');
        $this->command->info('   Gestionnaire: 2000000001 / password123');
        $this->command->info('   USVA: 3000000001 / password123');
        $this->command->info('   Direction: 4000000001 / password123');
        $this->command->info('   Admin: 1000000002 / admin123');
    }

    /**
     * Create random users for testing and development.
     */
    private function createRandomUsers(): void
    {
        // Create active users for each role
        Utilisateur::factory(15)->eleveur()->actif()->create();
        Utilisateur::factory(8)->gestionnaire()->actif()->create();
        Utilisateur::factory(5)->usva()->actif()->create();
        Utilisateur::factory(3)->direction()->actif()->create();

        // Create some inactive users
        Utilisateur::factory(5)->eleveur()->inactif()->create();
        Utilisateur::factory(2)->gestionnaire()->inactif()->create();

        // Create some users with mixed roles and statuses
        Utilisateur::factory(10)->create();

        $this->command->info('✅ Random users created successfully!');
        $this->command->info('📊 Total users created: ' . Utilisateur::count());
        $this->command->info('📊 Active users: ' . Utilisateur::where('statut', 'actif')->count());
        $this->command->info('📊 Inactive users: ' . Utilisateur::where('statut', 'inactif')->count());
        
        // Show role distribution
        foreach (['éleveur', 'gestionnaire', 'usva', 'direction'] as $role) {
            $count = Utilisateur::where('role', $role)->count();
            $this->command->info("📊 {$role}: {$count} users");
        }
    }
}