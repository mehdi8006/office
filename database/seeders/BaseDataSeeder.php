<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use App\Models\Cooperative;
use App\Models\MembreEleveur;
use App\Models\PrixUnitaire;
use Illuminate\Support\Facades\Hash;

class BaseDataSeeder extends Seeder
{
    /**
     * Seeder pour créer les données de base nécessaires
     * À exécuter AVANT le Mois5Et6Seeder
     */
    public function run(): void
    {
        $this->command->info('🔧 Création des données de base...');
        
        // 1. Créer un prix unitaire si aucun n'existe
        $this->creerPrixUnitaire();
        
        // 2. Créer des utilisateurs de base
        $utilisateurs = $this->creerUtilisateurs();
        
        // 3. Créer des coopératives
        $cooperatives = $this->creerCooperatives($utilisateurs);
        
        // 4. Créer des membres éleveurs
        $this->creerMembresEleveurs($cooperatives);
        
        $this->command->info('✅ Données de base créées avec succès!');
    }
    
    private function creerPrixUnitaire()
    {
        if (PrixUnitaire::count() == 0) {
            PrixUnitaire::create(['prix' => 3.50]);
            $this->command->info('💰 Prix unitaire créé: 3.50 DH/L');
        } else {
            $this->command->info('💰 Prix unitaire existant trouvé');
        }
    }
    
    private function creerUtilisateurs()
    {
        $this->command->info('👥 Création des utilisateurs...');
        
        $utilisateurs = [];
        
        // Utilisateur direction
        $utilisateurs['direction'] = Utilisateur::firstOrCreate(
            ['email' => 'direction@laiterie.ma'],
            [
                'nom_complet' => 'Directeur Général',
                'mot_de_passe' => Hash::make('password'),
                'telephone' => '0661234567',
                'role' => 'direction',
                'statut' => 'actif',
            ]
        );
        
        // Utilisateur USVA
        $utilisateurs['usva'] = Utilisateur::firstOrCreate(
            ['email' => 'usva@laiterie.ma'],
            [
                'nom_complet' => 'Agent USVA',
                'mot_de_passe' => Hash::make('password'),
                'telephone' => '0661234568',
                'role' => 'usva',
                'statut' => 'actif',
            ]
        );
        
        // Gestionnaires pour les coopératives
        for ($i = 1; $i <= 5; $i++) {
            $utilisateurs["gestionnaire_{$i}"] = Utilisateur::firstOrCreate(
                ['email' => "gestionnaire{$i}@laiterie.ma"],
                [
                    'nom_complet' => "Gestionnaire Coopérative {$i}",
                    'mot_de_passe' => Hash::make('password'),
                    'telephone' => '066123456' . $i,
                    'role' => 'gestionnaire',
                    'statut' => 'actif',
                ]
            );
        }
        
        $this->command->info("  ✅ " . count($utilisateurs) . " utilisateurs créés/vérifiés");
        return $utilisateurs;
    }
    
    private function creerCooperatives($utilisateurs)
    {
        $this->command->info('🏢 Création des coopératives...');
        
        $nomsCooperatives = [
            'Coopérative Al Manar',
            'Coopérative Assalam',
            'Coopérative Al Baraka',
            'Coopérative Nour',
            'Coopérative Al Amal'
        ];
        
        $villes = [
            'Casablanca',
            'Rabat', 
            'Fès',
            'Marrakech',
            'Meknès'
        ];
        
        $cooperatives = [];
        
        for ($i = 0; $i < count($nomsCooperatives); $i++) {
            $gestionnaire = $utilisateurs["gestionnaire_" . ($i + 1)];
            
            $cooperatives[] = Cooperative::firstOrCreate(
                ['email' => "coop" . ($i + 1) . "@laiterie.ma"],
                [
                    'nom_cooperative' => $nomsCooperatives[$i],
                    'adresse' => "Quartier Industriel, {$villes[$i]}, Maroc",
                    'telephone' => '0522' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                    'statut' => 'actif',
                    'responsable_id' => $gestionnaire->id_utilisateur,
                ]
            );
        }
        
        $this->command->info("  ✅ " . count($cooperatives) . " coopératives créées/vérifiées");
        return $cooperatives;
    }
    
    private function creerMembresEleveurs($cooperatives)
    {
        $this->command->info('🐄 Création des membres éleveurs...');
        
        $prenoms = ['Ahmed', 'Mohamed', 'Youssef', 'Hassan', 'Ali', 'Omar', 'Said', 'Khalid', 'Rachid', 'Abdelkarim'];
        $noms = ['Benali', 'Alaoui', 'Bennani', 'Tazi', 'Fassi', 'Idrissi', 'Bennani', 'Alami', 'Berrada', 'Chraibi'];
        
        $totalMembres = 0;
        
        foreach ($cooperatives as $cooperative) {
            // Chaque coopérative a entre 8 et 15 membres
            $nombreMembres = rand(8, 15);
            
            for ($i = 0; $i < $nombreMembres; $i++) {
                $prenom = $prenoms[array_rand($prenoms)];
                $nom = $noms[array_rand($noms)];
                $nomComplet = "{$prenom} {$nom}";
                
                // Générer un numéro de carte nationale unique
                do {
                    $numeroCarte = 'AB' . rand(100000, 999999);
                } while (MembreEleveur::where('numero_carte_nationale', $numeroCarte)->exists());
                
                // Générer un email unique
                $email = strtolower(str_replace(' ', '.', $nomComplet)) . rand(1, 999) . '@gmail.com';
                
                // Vérifier que l'email n'existe pas déjà
                while (MembreEleveur::where('email', $email)->exists()) {
                    $email = strtolower(str_replace(' ', '.', $nomComplet)) . rand(1, 9999) . '@gmail.com';
                }
                
                MembreEleveur::create([
                    'id_cooperative' => $cooperative->id_cooperative,
                    'nom_complet' => $nomComplet,
                    'adresse' => "Douar " . chr(65 + $i) . ", Commune Rurale, Maroc",
                    'telephone' => '067' . rand(1000000, 9999999),
                    'email' => $email,
                    'numero_carte_nationale' => $numeroCarte,
                    'statut' => rand(1, 10) <= 9 ? 'actif' : 'inactif', // 90% actifs
                ]);
                
                $totalMembres++;
            }
        }
        
        $this->command->info("  ✅ {$totalMembres} membres éleveurs créés");
    }
}