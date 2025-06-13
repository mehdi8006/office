<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;

class GetGestionnaireCredentialsSeeder extends Seeder
{
    /**
     * Affiche les identifiants des gestionnaires
     */
    public function run(): void
    {
        $this->command->info('🔑 === IDENTIFIANTS GESTIONNAIRES ===');
        
        $gestionnaires = Utilisateur::where('role', 'gestionnaire')
                                  ->with('cooperativeGeree')
                                  ->orderBy('id_utilisateur')
                                  ->get();
        
        if ($gestionnaires->isEmpty()) {
            $this->command->error('❌ Aucun gestionnaire trouvé. Exécutez d\'abord BaseDataSeeder.');
            return;
        }
        
        foreach ($gestionnaires as $index => $gestionnaire) {
            $cooperative = $gestionnaire->cooperativeGeree;
            $nomCooperative = $cooperative ? $cooperative->nom_cooperative : 'Non assignée';
            
            $this->command->info("\n📋 GESTIONNAIRE " . ($index + 1) . ":");
            $this->command->line("   Matricule: {$gestionnaire->matricule}");
            $this->command->line("   Email: {$gestionnaire->email}");
            $this->command->line("   Nom: {$gestionnaire->nom_complet}");
            $this->command->line("   Mot de passe: password");
            $this->command->line("   Coopérative: {$nomCooperative}");
            $this->command->line("   Statut: {$gestionnaire->statut}");
        }
        
        $this->command->info("\n💡 Vous pouvez vous connecter avec soit :");
        $this->command->info("   • Le matricule + mot de passe");
        $this->command->info("   • L'email + mot de passe");
        
        // Afficher aussi les autres rôles
        $this->afficherAutresRoles();
    }
    
    private function afficherAutresRoles()
    {
        $this->command->info("\n🎭 === AUTRES RÔLES DISPONIBLES ===");
        
        $autresRoles = Utilisateur::whereIn('role', ['direction', 'usva'])->get();
        
        foreach ($autresRoles as $user) {
            $this->command->info("\n📋 " . strtoupper($user->role) . ":");
            $this->command->line("   Matricule: {$user->matricule}");
            $this->command->line("   Email: {$user->email}");
            $this->command->line("   Nom: {$user->nom_complet}");
            $this->command->line("   Mot de passe: password");
        }
    }
}