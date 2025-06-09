<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Utilisateur;
use App\Models\Cooperative;
use App\Models\MembreEleveur;
use App\Models\ReceptionLait;
use App\Models\LivraisonUsine;
use App\Models\PaiementCooperativeUsine;
use App\Models\PaiementCooperativeEleveur;
use App\Models\StockLait;
use Carbon\Carbon;

/**
 * Seeder pour créer des scénarios de test spécifiques
 */
class TestScenariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🧪 Création des scénarios de test...');

        // 1. Scénario: Coopérative performante
        $this->createSuccessfulCooperative();

        // 2. Scénario: Coopérative avec problèmes
        $this->createProblematicCooperative();

        // 3. Scénario: Nouveau membre avec historique récent
        $this->createNewMemberScenario();

        // 4. Scénario: Paiements en retard
        $this->createLatePaymentScenario();

        // 5. Scénario: Livraisons importantes
        $this->createBigDeliveryScenario();

        // 6. Scénario: Coopérative saisonnière
        $this->createSeasonalCooperative();

        $this->command->info('✅ Scénarios de test créés avec succès!');
    }

    /**
     * Scénario 1: Coopérative très performante
     */
    private function createSuccessfulCooperative(): void
    {
        $this->command->info('   🌟 Création coopérative performante...');

        // Gestionnaire expérimenté
        $gestionnaire = Utilisateur::factory()->gestionnaire()->create([
            'nom_complet' => 'Hassan Bencherif',
            'email' => 'h.bencherif@coop-atlas.ma',
            'statut' => 'actif'
        ]);

        // Coopérative de référence
        $cooperative = Cooperative::factory()->actif()->create([
            'nom_cooperative' => 'Coopérative Laitière Atlas Excellence',
            'responsable_id' => $gestionnaire->id_utilisateur,
            'adresse' => '123 Avenue Mohammed V, Hay Al Massira, Settat',
            'email' => 'contact@coop-atlas-excellence.ma'
        ]);

        // Membres productifs (15 membres actifs)
        $membres = MembreEleveur::factory()
            ->count(15)
            ->actif()
            ->forCooperative($cooperative)
            ->create();

        // Générer des réceptions importantes et régulières (3 derniers mois)
        $dateDebut = Carbon::now()->subMonths(3);
        $dateFin = Carbon::now();

        foreach ($membres as $membre) {
            $currentDate = $dateDebut->copy();
            
            while ($currentDate <= $dateFin) {
                if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                    // 95% de régularité
                    if (fake()->boolean(95)) {
                        ReceptionLait::factory()
                            ->forMembre($membre)
                            ->onDate($currentDate)
                            ->grandEleveur() // Gros volumes
                            ->create();
                    }
                }
                $currentDate->addDay();
            }
        }

        // Livraisons régulières et bien payées
        $this->createRegularDeliveries($cooperative, $dateDebut, $dateFin);

        // Paiements éleveurs à jour
        $this->createCurrentPayments($cooperative, $dateDebut, $dateFin);

        $this->command->info('   ✓ Coopérative performante créée');
    }

    /**
     * Scénario 2: Coopérative avec problèmes
     */
    private function createProblematicCooperative(): void
    {
        $this->command->info('   ⚠️ Création coopérative problématique...');

        // Coopérative sans gestionnaire
        $cooperative = Cooperative::factory()->actif()->create([
            'nom_cooperative' => 'Coopérative Rurale Difficultés',
            'responsable_id' => null,
            'adresse' => 'Douar Oulad Ahmed, Commune Rurale, Province de Settat'
        ]);

        // Quelques membres avec production irrégulière
        $membres = MembreEleveur::factory()
            ->count(8)
            ->actif()
            ->forCooperative($cooperative)
            ->create();

        // Réceptions irrégulières et faibles
        $dateDebut = Carbon::now()->subMonths(2);
        $dateFin = Carbon::now();

        foreach ($membres as $membre) {
            $currentDate = $dateDebut->copy();
            
            while ($currentDate <= $dateFin) {
                // Seulement 60% de régularité
                if (fake()->boolean(60)) {
                    ReceptionLait::factory()
                        ->forMembre($membre)
                        ->onDate($currentDate)
                        ->petitEleveur() // Petits volumes
                        ->create();
                }
                $currentDate->addDays(fake()->numberBetween(1, 3)); // Irrégulier
            }
        }

        // Livraisons sporadiques
        $this->createSporadicDeliveries($cooperative, $dateDebut, $dateFin);

        // Paiements en retard
        $this->createDelayedPayments($cooperative);

        $this->command->info('   ✓ Coopérative problématique créée');
    }

    /**
     * Scénario 3: Nouveau membre avec historique récent
     */
    private function createNewMemberScenario(): void
    {
        $this->command->info('   👶 Création scénario nouveau membre...');

        // Prendre une coopérative existante
        $cooperative = Cooperative::actif()->first();
        
        if (!$cooperative) {
            $cooperative = Cooperative::factory()->actif()->create();
        }

        // Nouveau membre (inscrit il y a 1 mois)
        $nouveauMembre = MembreEleveur::factory()
            ->actif()
            ->forCooperative($cooperative)
            ->create([
                'nom_complet' => 'Youssef El Jamali',
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now()->subMonth()
            ]);

        // Réceptions depuis 1 mois avec progression
        $dateDebut = Carbon::now()->subMonth();
        $dateFin = Carbon::now();
        $currentDate = $dateDebut->copy();

        $quantiteInitiale = 15; // Commence petit
        $semaine = 0;

        while ($currentDate <= $dateFin) {
            if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                // Progression de 5% par semaine
                $facteurProgression = 1 + ($semaine * 0.05);
                $quantite = round($quantiteInitiale * $facteurProgression, 2);

                ReceptionLait::factory()
                    ->forMembre($nouveauMembre)
                    ->onDate($currentDate)
                    ->withQuantite($quantite)
                    ->create();
            }

            if ($currentDate->dayOfWeek === Carbon::SUNDAY) {
                $semaine++;
            }

            $currentDate->addDay();
        }

        $this->command->info('   ✓ Scénario nouveau membre créé');
    }

    /**
     * Scénario 4: Paiements en retard
     */
    private function createLatePaymentScenario(): void
    {
        $this->command->info('   ⏰ Création scénario paiements en retard...');

        // Coopérative avec problèmes financiers
        $cooperative = Cooperative::factory()->actif()->create([
            'nom_cooperative' => 'Coopérative Retards Paiements'
        ]);

        $membres = MembreEleveur::factory()
            ->count(5)
            ->actif()
            ->forCooperative($cooperative)
            ->create();

        // Créer des paiements en retard (3 mois non payés)
        for ($i = 3; $i >= 1; $i--) {
            $moisDebut = Carbon::now()->subMonths($i)->startOfMonth();
            $moisFin = $moisDebut->copy()->endOfMonth();

            foreach ($membres as $membre) {
                // Simuler des réceptions pour ce mois
                $quantiteMensuelle = fake()->randomFloat(2, 200, 800);

                PaiementCooperativeEleveur::factory()
                    ->forMembre($membre)
                    ->create([
                        'periode_debut' => $moisDebut,
                        'periode_fin' => $moisFin,
                        'quantite_totale' => $quantiteMensuelle,
                        'statut' => 'calcule', // Calculé mais pas payé
                        'date_paiement' => null
                    ]);
            }
        }

        // Livraisons récentes mais non payées par l'usine
        $livraisons = LivraisonUsine::factory()
            ->count(5)
            ->forCooperative($cooperative)
            ->recent()
            ->validee()
            ->create();

        foreach ($livraisons as $livraison) {
            PaiementCooperativeUsine::factory()
                ->forLivraison($livraison)
                ->enRetard()
                ->create();
        }

        $this->command->info('   ✓ Scénario paiements en retard créé');
    }

    /**
     * Scénario 5: Livraisons importantes
     */
    private function createBigDeliveryScenario(): void
    {
        $this->command->info('   🚛 Création scénario grosses livraisons...');

        // Grande coopérative
        $cooperative = Cooperative::factory()->actif()->create([
            'nom_cooperative' => 'Coopérative Industrielle Maroc'
        ]);

        // Beaucoup de membres productifs
        $membres = MembreEleveur::factory()
            ->count(25)
            ->actif()
            ->forCooperative($cooperative)
            ->create();

        // Génération de gros stocks cette semaine
        $dateDebut = Carbon::now()->startOfWeek();
        $dateFin = Carbon::now();

        foreach ($membres as $membre) {
            $currentDate = $dateDebut->copy();
            
            while ($currentDate <= $dateFin) {
                if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                    ReceptionLait::factory()
                        ->forMembre($membre)
                        ->onDate($currentDate)
                        ->grandEleveur()
                        ->create();
                }
                $currentDate->addDay();
            }
        }

        // Mettre à jour les stocks
        $currentDate = $dateDebut->copy();
        while ($currentDate <= $dateFin) {
            StockLait::updateDailyStock($cooperative->id_cooperative, $currentDate);
            $currentDate->addDay();
        }

        // Créer des livraisons exceptionnellement importantes
        LivraisonUsine::factory()
            ->count(3)
            ->forCooperative($cooperative)
            ->thisWeek()
            ->grande()
            ->validee()
            ->create();

        $this->command->info('   ✓ Scénario grosses livraisons créé');
    }

    /**
     * Scénario 6: Coopérative saisonnière
     */
    private function createSeasonalCooperative(): void
    {
        $this->command->info('   🌱 Création coopérative saisonnière...');

        $cooperative = Cooperative::factory()->actif()->create([
            'nom_cooperative' => 'Coopérative Saisonnière Atlas'
        ]);

        $membres = MembreEleveur::factory()
            ->count(12)
            ->actif()
            ->forCooperative($cooperative)
            ->create();

        // Créer un historique sur 12 mois avec variations saisonnières prononcées
        for ($mois = 12; $mois >= 1; $mois--) {
            $dateDebut = Carbon::now()->subMonths($mois)->startOfMonth();
            $dateFin = $dateDebut->copy()->endOfMonth();

            // Facteur saisonnier exagéré
            $facteurSaison = match($dateDebut->month) {
                3, 4, 5 => 2.0,    // Printemps: double production
                6, 7, 8 => 0.4,    // Été: très faible
                9, 10, 11 => 1.2,  // Automne: bonne
                default => 0.3     // Hiver: quasi arrêt
            };

            foreach ($membres as $membre) {
                $currentDate = $dateDebut->copy();
                
                while ($currentDate <= $dateFin) {
                    if ($currentDate->dayOfWeek !== Carbon::SUNDAY && fake()->boolean(80)) {
                        $quantiteBase = fake()->randomFloat(2, 20, 60);
                        $quantiteFinale = round($quantiteBase * $facteurSaison, 2);

                        if ($quantiteFinale >= 5) { // Minimum 5L
                            ReceptionLait::factory()
                                ->forMembre($membre)
                                ->onDate($currentDate)
                                ->withQuantite($quantiteFinale)
                                ->create();
                        }
                    }
                    $currentDate->addDay();
                }
            }
        }

        $this->command->info('   ✓ Coopérative saisonnière créée');
    }

    /**
     * Créer des livraisons régulières
     */
    private function createRegularDeliveries($cooperative, $dateDebut, $dateFin): void
    {
        $currentDate = $dateDebut->copy();
        
        while ($currentDate <= $dateFin) {
            if (in_array($currentDate->dayOfWeek, [Carbon::TUESDAY, Carbon::FRIDAY])) {
                LivraisonUsine::factory()
                    ->forCooperative($cooperative)
                    ->onDate($currentDate)
                    ->grande()
                    ->payee()
                    ->create();
            }
            $currentDate->addDay();
        }
    }

    /**
     * Créer des livraisons sporadiques
     */
    private function createSporadicDeliveries($cooperative, $dateDebut, $dateFin): void
    {
        $currentDate = $dateDebut->copy();
        
        while ($currentDate <= $dateFin) {
            if (fake()->boolean(30)) { // Seulement 30% de chance
                LivraisonUsine::factory()
                    ->forCooperative($cooperative)
                    ->onDate($currentDate)
                    ->petite()
                    ->planifiee()
                    ->create();
            }
            $currentDate->addDays(fake()->numberBetween(2, 7)); // Irrégulier
        }
    }

    /**
     * Créer des paiements à jour
     */
    private function createCurrentPayments($cooperative, $dateDebut, $dateFin): void
    {
        $moisCourant = $dateDebut->copy()->startOfMonth();
        
        while ($moisCourant < $dateFin->subDays(10)) {
            $membres = $cooperative->membresActifs;
            
            foreach ($membres as $membre) {
                PaiementCooperativeEleveur::factory()
                    ->forMembre($membre)
                    ->create([
                        'periode_debut' => $moisCourant->copy(),
                        'periode_fin' => $moisCourant->copy()->endOfMonth(),
                        'statut' => 'paye',
                        'date_paiement' => $moisCourant->copy()->addDays(15)
                    ]);
            }
            
            $moisCourant->addMonth();
        }
    }

    /**
     * Créer des paiements en retard
     */
    private function createDelayedPayments($cooperative): void
    {
        $membres = $cooperative->membresActifs;
        
        for ($i = 2; $i >= 1; $i--) {
            $moisDebut = Carbon::now()->subMonths($i)->startOfMonth();
            $moisFin = $moisDebut->copy()->endOfMonth();
            
            foreach ($membres as $membre) {
                PaiementCooperativeEleveur::factory()
                    ->forMembre($membre)
                    ->enRetard()
                    ->create([
                        'periode_debut' => $moisDebut,
                        'periode_fin' => $moisFin
                    ]);
            }
        }
    }
}