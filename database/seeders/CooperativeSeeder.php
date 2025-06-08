<?php

namespace Database\Seeders;

use App\Models\Cooperative;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

class CooperativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏭 Création des coopératives laitières...');

        // Vérifier s'il y a des utilisateurs disponibles pour être responsables
        $utilisateursDisponibles = Utilisateur::whereIn('role', ['gestionnaire', 'direction'])
            ->where('statut', 'actif')
            ->get();

        $nombreUtilisateurs = $utilisateursDisponibles->count();
        
        if ($nombreUtilisateurs > 0) {
            $this->command->info("✅ {$nombreUtilisateurs} utilisateurs trouvés pour assigner comme responsables");
        } else {
            $this->command->warn("⚠️  Aucun utilisateur gestionnaire trouvé. Les coopératives seront créées sans responsable.");
        }

        // Configuration de la répartition géographique réaliste
        $repartitionRegions = $this->obtenirRepartitionRegions();
        
        $totalCooperatives = 0;
        $cooperativesCreees = [];

        foreach ($repartitionRegions as $region => $config) {
            $this->command->info("📍 Création des coopératives pour la région: {$region}");
            
            $nombrePourRegion = $config['nombre'];
            $cooperativesRegion = $this->creerCooperativesPourRegion($region, $nombrePourRegion, $utilisateursDisponibles);
            
            $cooperativesCreees[$region] = $cooperativesRegion;
            $totalCooperatives += count($cooperativesRegion);
            
            $this->command->info("   ✅ {$nombrePourRegion} coopératives créées dans {$region}");
        }

        // Créer quelques coopératives supplémentaires avec responsables spécifiques
        if ($nombreUtilisateurs > 0) {
            $cooperativesSpeciales = $this->creerCooperativesSpeciales($utilisateursDisponibles);
            $totalCooperatives += count($cooperativesSpeciales);
        }

        $this->afficherStatistiques($totalCooperatives, $cooperativesCreees);
    }

    /**
     * Obtenir la répartition réaliste des coopératives par région
     */
    private function obtenirRepartitionRegions(): array
    {
        return [
            'Casablanca-Settat' => [
                'nombre' => 4,
                'description' => 'Région économique principale, fortes coopératives'
            ],
            'Rabat-Salé-Kénitra' => [
                'nombre' => 3,
                'description' => 'Région administrative, coopératives modernes'
            ],
            'Fès-Meknès' => [
                'nombre' => 3,
                'description' => 'Région agricole traditionnelle'
            ],
            'Béni Mellal-Khénifra' => [
                'nombre' => 3,
                'description' => 'Zone d\'élevage intensive'
            ],
            'Marrakech-Safi' => [
                'nombre' => 2,
                'description' => 'Coopératives touristiques et traditionnelles'
            ],
            'Souss-Massa' => [
                'nombre' => 2,
                'description' => 'Agriculture moderne et export'
            ],
            'Oriental' => [
                'nombre' => 2,
                'description' => 'Élevage extensif'
            ],
            'Tanger-Tétouan-Al Hoceïma' => [
                'nombre' => 2,
                'description' => 'Coopératives du nord'
            ],
            'Drâa-Tafilalet' => [
                'nombre' => 1,
                'description' => 'Oasis et élevage traditionnel'
            ]
        ];
    }

    /**
     * Créer les coopératives pour une région donnée
     */
    private function creerCooperativesPourRegion(string $region, int $nombre, $utilisateurs): array
    {
        $cooperatives = [];
        
        for ($i = 0; $i < $nombre; $i++) {
            // Déterminer si cette coopérative aura un responsable
            $avecResponsable = !$utilisateurs->isEmpty() && rand(1, 100) <= 70; // 70% avec responsable
            
            // Déterminer le statut (90% actives, 10% inactives)
            $statut = rand(1, 100) <= 90 ? 'actif' : 'inactif';
            
            // Créer la coopérative
            $cooperative = Cooperative::factory()
                ->dansRegion($region)
                ->state(['statut' => $statut]);

            // Assigner un responsable si disponible
            if ($avecResponsable) {
                $responsable = $utilisateurs->random();
                $cooperative = $cooperative->avecResponsableSpecifique($responsable->id_utilisateur);
            }

            $coop = $cooperative->create();
            $cooperatives[] = $coop;
        }

        return $cooperatives;
    }

    /**
     * Créer quelques coopératives spéciales avec des caractéristiques particulières
     */
    private function creerCooperativesSpeciales($utilisateurs): array
    {
        $cooperativesSpeciales = [];

        // 1. Coopérative pilote récente
        $coopPilote = Cooperative::factory()
            ->recente()
            ->active()
            ->avecResponsableSpecifique($utilisateurs->first()->id_utilisateur)
            ->create();
        
        $cooperativesSpeciales[] = $coopPilote;

        // 2. Grande coopérative historique
        if ($utilisateurs->count() > 1) {
            $grandeCooperative = Cooperative::factory()
                ->state([
                    'nom_cooperative' => 'Coopérative Centrale du Lait Marocain',
                    'created_at' => now()->subYears(5),
                    'statut' => 'actif'
                ])
                ->avecResponsableSpecifique($utilisateurs->skip(1)->first()->id_utilisateur)
                ->create();
                
            $cooperativesSpeciales[] = $grandeCooperative;
        }

        // 3. Coopérative en difficulté (inactive)
        if ($utilisateurs->count() > 2) {
            $coopDifficulte = Cooperative::factory()
                ->inactive()
                ->state([
                    'nom_cooperative' => 'Coopérative Al Tadamon (En Restructuration)',
                    'updated_at' => now()->subMonths(6)
                ])
                ->create(); // Sans responsable volontairement
                
            $cooperativesSpeciales[] = $coopDifficulte;
        }

        return $cooperativesSpeciales;
    }

    /**
     * Afficher les statistiques détaillées de création
     */
    private function afficherStatistiques(int $total, array $cooperativesParRegion): void
    {
        $this->command->info("\n✅ {$total} coopératives créées avec succès!");

        // Statistiques par statut
        $actives = Cooperative::where('statut', 'actif')->count();
        $inactives = Cooperative::where('statut', 'inactif')->count();
        
        $this->command->info("\n📊 Répartition par statut:");
        $this->command->info("   - Actives: {$actives} (" . round(($actives/$total)*100, 1) . "%)");
        $this->command->info("   - Inactives: {$inactives} (" . round(($inactives/$total)*100, 1) . "%)");

        // Statistiques responsables
        $avecResponsable = Cooperative::whereNotNull('responsable_id')->count();
        $sansResponsable = Cooperative::whereNull('responsable_id')->count();
        
        $this->command->info("\n👥 Répartition des responsables:");
        $this->command->info("   - Avec responsable: {$avecResponsable}");
        $this->command->info("   - Sans responsable: {$sansResponsable}");

        // Répartition géographique
        $this->command->info("\n🗺️  Répartition géographique:");
        foreach ($cooperativesParRegion as $region => $cooperatives) {
            $nombreCoops = count($cooperatives);
            $pourcentage = round(($nombreCoops/$total)*100, 1);
            $this->command->info("   - {$region}: {$nombreCoops} coopératives ({$pourcentage}%)");
        }

        // Informations sur les responsables assignés
        $this->afficherInfoResponsables();

        // Top 5 des coopératives par ancienneté
        $this->afficherTopCooperatives();

        // Conseils pour la suite
        $this->afficherConseilsSuite();
    }

    /**
     * Afficher les informations sur les responsables
     */
    private function afficherInfoResponsables(): void
    {
        $responsables = Cooperative::join('utilisateurs', 'cooperatives.responsable_id', '=', 'utilisateurs.id_utilisateur')
            ->select('utilisateurs.nom_complet', 'utilisateurs.role', 'cooperatives.nom_cooperative')
            ->get();

        if ($responsables->isNotEmpty()) {
            $this->command->info("\n🎯 Responsables assignés:");
            foreach ($responsables->take(5) as $resp) {
                $this->command->info("   - {$resp->nom_complet} ({$resp->role}) → {$resp->nom_cooperative}");
            }
            
            if ($responsables->count() > 5) {
                $reste = $responsables->count() - 5;
                $this->command->info("   ... et {$reste} autres assignations");
            }
        }
    }

    /**
     * Afficher le top des coopératives
     */
    private function afficherTopCooperatives(): void
    {
        $this->command->info("\n🏆 Top 5 des coopératives les plus anciennes:");
        
        $anciennes = Cooperative::orderBy('created_at', 'asc')->limit(5)->get();
        
        foreach ($anciennes as $index => $coop) {
            $anciennete = $coop->created_at->diffForHumans();
            $statut = $coop->statut === 'actif' ? '✅' : '❌';
            
            $this->command->info(sprintf(
                "   %d. %s %s (créée %s)",
                $index + 1,
                $statut,
                $coop->nom_cooperative,
                $anciennete
            ));
        }
    }

    /**
     * Afficher les conseils pour la suite
     */
    private function afficherConseilsSuite(): void
    {
        $this->command->info("\n💡 Prochaines étapes recommandées:");
        $this->command->info("   1. Exécuter MembreEleveurSeeder pour peupler les coopératives");
        $this->command->info("   2. Vérifier les relations avec: php artisan tinker");
        $this->command->info("      >>> Cooperative::with('responsable')->get()");
        $this->command->info("   3. Tester les requêtes de base sur les coopératives");
        
        // Exemples de requêtes utiles
        $this->command->info("\n🔍 Requêtes utiles pour tester:");
        $this->command->info("   - Coopératives actives: Cooperative::where('statut', 'actif')->count()");
        $this->command->info("   - Par région: Cooperative::where('adresse', 'like', '%Casablanca%')->get()");
        $this->command->info("   - Avec responsables: Cooperative::whereNotNull('responsable_id')->with('responsable')->get()");

        // Statistiques finales
        $totalUtilisateurs = Utilisateur::count();
        $totalCooperatives = Cooperative::count();
        
        $this->command->info("\n📈 État actuel de la base de données:");
        $this->command->info("   - Utilisateurs: {$totalUtilisateurs}");
        $this->command->info("   - Coopératives: {$totalCooperatives}");
        $this->command->info("   - Prêt pour les membres éleveurs: ✅");
    }
}