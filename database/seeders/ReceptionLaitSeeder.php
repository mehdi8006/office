<?php

namespace Database\Seeders;

use App\Models\MembreEleveur;
use App\Models\ReceptionLait;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReceptionLaitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer seulement les membres actifs
        $membresActifs = MembreEleveur::where('statut', 'actif')->get();

        if ($membresActifs->isEmpty()) {
            $this->command->warn('Aucun membre éleveur actif trouvé. Veuillez d\'abord exécuter MembreEleveurSeeder.');
            return;
        }

        $this->command->info('Création de l\'historique des réceptions de lait...');
        $this->command->info("Membres actifs trouvés: {$membresActifs->count()}");

        // Période de génération : 6 mois d'historique
        $dateDebut = Carbon::now()->subMonths(6);
        $dateFin = Carbon::now();

        $totalReceptions = 0;
        $progressBar = $this->command->getOutput()->createProgressBar($membresActifs->count());
        $progressBar->start();

        foreach ($membresActifs as $membre) {
            $receptionsCreees = $this->creerReceptionsPourMembre($membre, $dateDebut, $dateFin);
            $totalReceptions += $receptionsCreees;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();

        // Statistiques finales
        $this->afficherStatistiques($totalReceptions, $dateDebut, $dateFin);
    }

    /**
     * Créer les réceptions pour un membre donné
     */
    private function creerReceptionsPourMembre(MembreEleveur $membre, Carbon $dateDebut, Carbon $dateFin): int
    {
        $receptionsCreees = 0;
        $dateActuelle = $dateDebut->copy();

        // Profil de production du membre (cohérent)
        $profilProduction = $this->determinerProfilProduction();

        while ($dateActuelle->lte($dateFin)) {
            // Probabilité de livrer selon le jour de la semaine
            $probabiliteLivraison = $this->getProbabiliteLivraison($dateActuelle, $profilProduction);

            if (rand(1, 100) <= $probabiliteLivraison) {
                $this->creerReception($membre, $dateActuelle->copy(), $profilProduction);
                $receptionsCreees++;
            }

            $dateActuelle->addDay();
        }

        return $receptionsCreees;
    }

    /**
     * Déterminer le profil de production d'un éleveur
     */
    private function determinerProfilProduction(): array
    {
        $profils = [
            'petit_regulier' => [
                'poids' => 25,
                'quantite_min' => 5,
                'quantite_max' => 20,
                'regularite' => 85, // % de jours avec livraison
                'variation' => 0.3  // Faible variation
            ],
            'moyen_regulier' => [
                'poids' => 35,
                'quantite_min' => 15,
                'quantite_max' => 45,
                'regularite' => 90,
                'variation' => 0.25
            ],
            'moyen_irregulier' => [
                'poids' => 20,
                'quantite_min' => 10,
                'quantite_max' => 50,
                'regularite' => 65,
                'variation' => 0.4
            ],
            'grand_regulier' => [
                'poids' => 15,
                'quantite_min' => 40,
                'quantite_max' => 120,
                'regularite' => 95,
                'variation' => 0.2
            ],
            'grand_irregulier' => [
                'poids' => 5,
                'quantite_min' => 30,
                'quantite_max' => 150,
                'regularite' => 75,
                'variation' => 0.35
            ]
        ];

        // Sélection pondérée du profil
        $random = rand(1, 100);
        $cumul = 0;
        
        foreach ($profils as $type => $profil) {
            $cumul += $profil['poids'];
            if ($random <= $cumul) {
                return array_merge($profil, ['type' => $type]);
            }
        }

        return $profils['moyen_regulier']; // Fallback
    }

    /**
     * Obtenir la probabilité de livraison selon le jour
     */
    private function getProbabiliteLivraison(Carbon $date, array $profil): int
    {
        // Jour de la semaine (1 = lundi, 7 = dimanche)
        $jourSemaine = $date->dayOfWeek === 0 ? 7 : $date->dayOfWeek;

        // Réduction de livraison le weekend
        $facteurJour = match ($jourSemaine) {
            1, 2, 3, 4, 5 => 1.0,    // Semaine normale
            6 => 0.7,                // Samedi réduit
            7 => 0.4                 // Dimanche très réduit
        };

        // Variation saisonnière
        $mois = $date->month;
        $facteurSaison = match (true) {
            in_array($mois, [3, 4, 5]) => 1.2,      // Printemps +20%
            in_array($mois, [6, 7, 8]) => 0.8,      // Été -20%
            in_array($mois, [9, 10, 11]) => 1.1,    // Automne +10%
            default => 1.0                          // Hiver normal
        };

        return (int) ($profil['regularite'] * $facteurJour * $facteurSaison);
    }

    /**
     * Créer une réception individuelle
     */
    private function creerReception(MembreEleveur $membre, Carbon $date, array $profil): void
    {
        // Calcul de la quantité avec variations
        $quantiteBase = rand($profil['quantite_min'] * 100, $profil['quantite_max'] * 100) / 100;
        
        // Variation saisonnière
        $facteurSaison = $this->getFacteurSaisonnier($date);
        
        // Variation aléatoire quotidienne
        $variationAleatoire = 1 + (rand(-$profil['variation'] * 100, $profil['variation'] * 100) / 100);
        
        $quantiteFinale = max(2.0, round($quantiteBase * $facteurSaison * $variationAleatoire, 2));

        // Générer un matricule unique
        $matricule = $this->genererMatriculeUnique($date);

        ReceptionLait::create([
            'id_cooperative' => $membre->id_cooperative,
            'id_membre' => $membre->id_membre,
            'matricule_reception' => $matricule,
            'date_reception' => $date->format('Y-m-d'),
            'quantite_litres' => $quantiteFinale,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }

    /**
     * Obtenir le facteur saisonnier
     */
    private function getFacteurSaisonnier(Carbon $date): float
    {
        return match ($date->month) {
            3, 4, 5 => rand(115, 140) / 100,    // Printemps
            6, 7, 8 => rand(70, 95) / 100,      // Été
            9, 10, 11 => rand(100, 125) / 100,  // Automne
            default => rand(90, 115) / 100      // Hiver
        };
    }

    /**
     * Générer un matricule unique
     */
    private function genererMatriculeUnique(Carbon $date): string
    {
        do {
            $prefix = 'REC' . $date->format('ymd');
            $sequence = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $matricule = $prefix . $sequence;
        } while (ReceptionLait::where('matricule_reception', $matricule)->exists());

        return $matricule;
    }

    /**
     * Afficher les statistiques de création
     */
    private function afficherStatistiques(int $totalReceptions, Carbon $dateDebut, Carbon $dateFin): void
    {
        $this->command->info("✅ {$totalReceptions} réceptions de lait créées");
        $this->command->info("📅 Période: {$dateDebut->format('d/m/Y')} au {$dateFin->format('d/m/Y')}");

        // Statistiques par mois
        $this->command->info("\n📊 Répartition par mois:");
        $receptionsMois = ReceptionLait::selectRaw('YEAR(date_reception) as annee, MONTH(date_reception) as mois, COUNT(*) as total')
            ->whereBetween('date_reception', [$dateDebut, $dateFin])
            ->groupBy('annee', 'mois')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get();

        foreach ($receptionsMois as $mois) {
            $nomMois = Carbon::create($mois->annee, $mois->mois, 1)->locale('fr')->isoFormat('MMMM YYYY');
            $this->command->info("   - {$nomMois}: {$mois->total} réceptions");
        }

        // Statistiques de quantité
        $quantites = ReceptionLait::whereBetween('date_reception', [$dateDebut, $dateFin])
            ->selectRaw('
                SUM(quantite_litres) as total_litres,
                AVG(quantite_litres) as moyenne_litres,
                MIN(quantite_litres) as min_litres,
                MAX(quantite_litres) as max_litres
            ')
            ->first();

        $this->command->info("\n🥛 Statistiques de quantité:");
        $this->command->info("   - Total: " . number_format($quantites->total_litres, 2) . " litres");
        $this->command->info("   - Moyenne: " . number_format($quantites->moyenne_litres, 2) . " L/réception");
        $this->command->info("   - Min: " . number_format($quantites->min_litres, 2) . " litres");
        $this->command->info("   - Max: " . number_format($quantites->max_litres, 2) . " litres");
    }
}