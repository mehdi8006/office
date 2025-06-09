<?php

namespace Database\Seeders;

use App\Models\Cooperative;
use App\Models\MembreEleveur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembreEleveurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Création des membres éleveurs...');

        // Vérifier qu'il y a des coopératives
        $cooperatives = Cooperative::all();
        if ($cooperatives->isEmpty()) {
            throw new \Exception('❌ Aucune coopérative trouvée. Veuillez d\'abord exécuter CooperativeSeeder.');
        }

        $this->command->info("🏭 {$cooperatives->count()} coopératives trouvées");

        // Vérifier s'il y a déjà des membres
        $existingMembers = MembreEleveur::count();
        if ($existingMembers > 0) {
            $this->command->info("ℹ️  {$existingMembers} membres déjà présents");
            
            // Vérifier si on a assez de membres par coopérative
            $cooperativesSansMembres = $cooperatives->filter(function($coop) {
                return $coop->membres()->count() < 10; // Minimum 10 membres par coopérative
            });
            
            if ($cooperativesSansMembres->isEmpty()) {
                $this->command->info("✅ Toutes les coopératives ont suffisamment de membres");
                return;
            }
            
            $this->command->info("📝 {$cooperativesSansMembres->count()} coopératives ont besoin de plus de membres");
            $cooperatives = $cooperativesSansMembres;
        }

        $totalMembresCreated = 0;
        $progressBar = $this->command->getOutput()->createProgressBar($cooperatives->count());
        $progressBar->start();

        foreach ($cooperatives as $cooperative) {
            $membresCreated = $this->creerMembresPourCooperative($cooperative);
            $totalMembresCreated += $membresCreated;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);

        $this->afficherStatistiques($totalMembresCreated);
    }

    /**
     * Créer les membres pour une coopérative donnée
     */
    private function creerMembresPourCooperative(Cooperative $cooperative): int
    {
        // Vérifier combien de membres cette coopérative a déjà
        $membresExistants = $cooperative->membres()->count();
        
        // Déterminer combien créer
        $nombreTotal = $this->determinerNombreMembres($cooperative);
        $aCreer = max(0, $nombreTotal - $membresExistants);
        
        if ($aCreer === 0) {
            return 0;
        }

        try {
            // Distribution réaliste des statuts
            $repartition = $this->calculerRepartitionStatuts($aCreer);
            
            $created = 0;

            // Créer les membres actifs
            if ($repartition['actifs'] > 0) {
                MembreEleveur::factory()
                    ->count($repartition['actifs'])
                    ->actif()
                    ->create(['id_cooperative' => $cooperative->id_cooperative]);
                $created += $repartition['actifs'];
            }

            // Créer les membres inactifs
            if ($repartition['inactifs'] > 0) {
                MembreEleveur::factory()
                    ->count($repartition['inactifs'])
                    ->inactif()
                    ->create(['id_cooperative' => $cooperative->id_cooperative]);
                $created += $repartition['inactifs'];
            }

            // Créer les membres en suppression
            if ($repartition['suppression'] > 0) {
                MembreEleveur::factory()
                    ->count($repartition['suppression'])
                    ->enSuppression()
                    ->create(['id_cooperative' => $cooperative->id_cooperative]);
                $created += $repartition['suppression'];
            }

            return $created;

        } catch (\Exception $e) {
            $this->command->error("❌ Erreur pour {$cooperative->nom_cooperative}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Déterminer le nombre de membres selon la taille de la coopérative
     */
    private function determinerNombreMembres(Cooperative $cooperative): int
    {
        // Utiliser l'ID pour une répartition cohérente
        $seed = $cooperative->id_cooperative;
        mt_srand($seed);

        // Répartition selon différents types de coopératives
        $typeCooperative = mt_rand(1, 100);

        return match (true) {
            // 10% - Très petites coopératives (villages isolés)
            $typeCooperative <= 10 => mt_rand(8, 15),
            
            // 25% - Petites coopératives 
            $typeCooperative <= 35 => mt_rand(15, 30),
            
            // 40% - Moyennes coopératives
            $typeCooperative <= 75 => mt_rand(30, 60),
            
            // 20% - Grandes coopératives
            $typeCooperative <= 95 => mt_rand(60, 100),
            
            // 5% - Très grandes coopératives (centres urbains)
            default => mt_rand(100, 150)
        };
    }

    /**
     * Calculer la répartition des statuts
     */
    private function calculerRepartitionStatuts(int $total): array
    {
        // Répartition réaliste : 85% actifs, 10% inactifs, 5% suppression
        $actifs = (int) round($total * 0.85);
        $inactifs = (int) round($total * 0.10);
        $suppression = $total - $actifs - $inactifs; // Le reste

        return [
            'actifs' => $actifs,
            'inactifs' => $inactifs,
            'suppression' => max(0, $suppression)
        ];
    }

    /**
     * Afficher les statistiques détaillées
     */
    private function afficherStatistiques(int $created): void
    {
        $total = MembreEleveur::count();
        
        $this->command->info("✅ {$created} nouveaux membres créés");
        $this->command->info("📊 {$total} membres au total");

        // Statistiques par statut
        $stats = DB::table('membres_eleveurs')
            ->selectRaw('
                statut,
                COUNT(*) as nombre,
                ROUND(COUNT(*) * 100.0 / ?, 1) as pourcentage
            ', [$total])
            ->groupBy('statut')
            ->get();

        $this->command->info("\n📋 Répartition par statut:");
        foreach ($stats as $stat) {
            $emoji = match($stat->statut) {
                'actif' => '🟢',
                'inactif' => '🟡',
                'suppression' => '🔴',
                default => '⚪'
            };
            $this->command->info("   {$emoji} " . ucfirst($stat->statut) . ": {$stat->nombre} ({$stat->pourcentage}%)");
        }

        // Statistiques par coopérative
        $this->afficherRepartitionParCooperative();

        // Top 5 des plus grandes coopératives
        $this->afficherTopCooperatives();
    }

    /**
     * Afficher la répartition par coopérative
     */
    private function afficherRepartitionParCooperative(): void
    {
        $repartition = DB::table('membres_eleveurs')
            ->join('cooperatives', 'membres_eleveurs.id_cooperative', '=', 'cooperatives.id_cooperative')
            ->selectRaw('
                cooperatives.nom_cooperative,
                COUNT(*) as total_membres,
                SUM(CASE WHEN membres_eleveurs.statut = "actif" THEN 1 ELSE 0 END) as actifs
            ')
            ->groupBy('cooperatives.id_cooperative', 'cooperatives.nom_cooperative')
            ->orderBy('total_membres', 'DESC')
            ->get();

        $this->command->info("\n🏭 Répartition par coopérative:");
        foreach ($repartition as $coop) {
            $tauxActivite = $coop->total_membres > 0 ? round(($coop->actifs / $coop->total_membres) * 100, 1) : 0;
            $this->command->info("   📊 {$coop->nom_cooperative}: {$coop->total_membres} membres ({$coop->actifs} actifs - {$tauxActivite}%)");
        }
    }

    /**
     * Afficher le top 5 des coopératives par nombre de membres
     */
    private function afficherTopCooperatives(): void
    {
        $top = DB::table('membres_eleveurs')
            ->join('cooperatives', 'membres_eleveurs.id_cooperative', '=', 'cooperatives.id_cooperative')
            ->selectRaw('
                cooperatives.nom_cooperative,
                COUNT(*) as total_membres,
                SUM(CASE WHEN membres_eleveurs.statut = "actif" THEN 1 ELSE 0 END) as actifs,
                MAX(membres_eleveurs.created_at) as derniere_adhesion
            ')
            ->groupBy('cooperatives.id_cooperative', 'cooperatives.nom_cooperative')
            ->orderBy('total_membres', 'DESC')
            ->limit(5)
            ->get();

        $this->command->info("\n🏆 Top 5 des plus grandes coopératives:");
        foreach ($top as $index => $coop) {
            $rang = $index + 1;
            $derniereAdhesion = \Carbon\Carbon::parse($coop->derniere_adhesion)->diffForHumans();
            $this->command->info("   {$rang}. {$coop->nom_cooperative}");
            $this->command->info("      👥 {$coop->total_membres} membres ({$coop->actifs} actifs)");
            $this->command->info("      📅 Dernière adhésion: {$derniereAdhesion}");
        }

        // Conseils pour la suite
        $this->afficherConseils();
    }

    /**
     * Afficher les conseils pour la suite
     */
    private function afficherConseils(): void
    {
        $totalActifs = MembreEleveur::where('statut', 'actif')->count();
        
        $this->command->info("\n💡 PROCHAINES ÉTAPES:");
        $this->command->info("   ✅ {$totalActifs} membres actifs prêts pour les réceptions de lait");
        $this->command->info("   📝 Exécuter: ReceptionLaitSeeder pour générer l'historique");
        $this->command->info("   🔍 Tester: MembreEleveur::with('cooperative')->where('statut', 'actif')->first()");
        
        $cooperativesSansMembres = Cooperative::whereDoesntHave('membres')->count();
        if ($cooperativesSansMembres > 0) {
            $this->command->warn("⚠️  {$cooperativesSansMembres} coopératives sans membres détectées");
        }
    }
}