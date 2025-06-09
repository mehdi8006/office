<?php

namespace Database\Factories;

use App\Models\PaiementCooperativeEleveur;
use App\Models\Cooperative;
use App\Models\MembreEleveur;
use App\Models\ReceptionLait;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaiementCooperativeEleveur>
 */
class PaiementCooperativeEleveurFactory extends Factory
{
    protected $model = PaiementCooperativeEleveur::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $periodeDebut = $this->faker->dateTimeBetween('-6 months', '-1 month');
        $periodeFin = Carbon::parse($periodeDebut)->addMonth()->subDay(); // Période d'un mois
        
        return [
            'id_membre' => MembreEleveur::factory()->actif(),
            'id_cooperative' => function (array $attributes) {
                // Utiliser la coopérative du membre
                if (isset($attributes['id_membre'])) {
                    $membre = MembreEleveur::find($attributes['id_membre']);
                    return $membre ? $membre->id_cooperative : Cooperative::factory();
                }
                return Cooperative::factory();
            },
            'periode_debut' => $periodeDebut,
            'periode_fin' => $periodeFin,
            'quantite_totale' => $this->faker->randomFloat(2, 50, 2000), // Sera généralement recalculé
            'prix_unitaire' => $this->generatePrixEleveur($periodeDebut),
            'montant_total' => 0, // Calculé automatiquement par le modèle
            'date_paiement' => $this->faker->optional(0.7)->dateTimeBetween($periodeFin, 'now'), // 70% ont été payés
            'statut' => $this->faker->randomElement(['calcule', 'paye']),
        ];
    }

    /**
     * Générer un prix unitaire pour éleveur (généralement moins que le prix usine)
     */
    private function generatePrixEleveur($date): float
    {
        $mois = Carbon::parse($date)->month;
        
        // Prix de base payé aux éleveurs (marge coopérative déduite)
        $prixBase = 3.80; // Environ 90% du prix usine
        
        // Variation saisonnière
        $variationSaisonniere = match(true) {
            in_array($mois, [3, 4, 5]) => 0.95,    // Printemps: plus d'offre
            in_array($mois, [6, 7, 8]) => 1.05,    // Été: moins d'offre
            in_array($mois, [9, 10, 11]) => 1.00,  // Automne: stable
            default => 1.02                         // Hiver: légèrement plus
        };
        
        // Petite variation aléatoire
        $variationAleatoire = $this->faker->randomFloat(2, 0.98, 1.02);
        
        $prix = $prixBase * $variationSaisonniere * $variationAleatoire;
        
        return round($prix, 2);
    }

    /**
     * État pour paiement calculé (pas encore payé)
     */
    public function calcule(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'calcule',
            'date_paiement' => null,
        ]);
    }

    /**
     * État pour paiement effectué
     */
    public function paye(): static
    {
        return $this->state(function (array $attributes) {
            $periodeFin = Carbon::parse($attributes['periode_fin']);
            $datePaiement = $periodeFin->copy()->addDays($this->faker->numberBetween(5, 20)); // Paiement 5-20 jours après fin période

            return [
                'statut' => 'paye',
                'date_paiement' => $datePaiement,
            ];
        });
    }

    /**
     * État pour paiement mensuel (période d'un mois complet)
     */
    public function mensuel($mois, $annee): static
    {
        return $this->state(function (array $attributes) use ($mois, $annee) {
            $debut = Carbon::create($annee, $mois, 1);
            $fin = $debut->copy()->endOfMonth();

            return [
                'periode_debut' => $debut,
                'periode_fin' => $fin,
                'prix_unitaire' => $this->generatePrixEleveur($debut),
            ];
        });
    }

    /**
     * État pour paiement hebdomadaire
     */
    public function hebdomadaire($dateDebut): static
    {
        return $this->state(function (array $attributes) use ($dateDebut) {
            $debut = Carbon::parse($dateDebut)->startOfWeek();
            $fin = $debut->copy()->endOfWeek();

            return [
                'periode_debut' => $debut,
                'periode_fin' => $fin,
                'prix_unitaire' => $this->generatePrixEleveur($debut),
            ];
        });
    }

    /**
     * État pour paiement avec membre spécifique
     */
    public function forMembre(MembreEleveur $membre): static
    {
        return $this->state(fn (array $attributes) => [
            'id_membre' => $membre->id_membre,
            'id_cooperative' => $membre->id_cooperative,
        ]);
    }

    /**
     * État pour paiement avec coopérative spécifique
     */
    public function forCooperative(Cooperative $cooperative): static
    {
        return $this->state(function (array $attributes) use ($cooperative) {
            // Sélectionner un membre aléatoire de cette coopérative
            $membre = $cooperative->membresActifs()->inRandomOrder()->first();
            
            if (!$membre) {
                $membre = MembreEleveur::factory()->actif()->forCooperative($cooperative)->create();
            }

            return [
                'id_cooperative' => $cooperative->id_cooperative,
                'id_membre' => $membre->id_membre,
            ];
        });
    }

    /**
     * État pour paiement avec prix spécifique
     */
    public function withPrix(float $prix): static
    {
        return $this->state(fn (array $attributes) => [
            'prix_unitaire' => $prix,
        ]);
    }

    /**
     * État pour paiement récent
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            $periodeFin = $this->faker->dateTimeBetween('-1 month', '-1 week');
            $periodeDebut = Carbon::parse($periodeFin)->subMonth()->addDay();

            return [
                'periode_debut' => $periodeDebut,
                'periode_fin' => $periodeFin,
                'statut' => $this->faker->randomElement(['calcule', 'paye']),
            ];
        });
    }

    /**
     * État pour paiement en retard
     */
    public function enRetard(): static
    {
        return $this->state(function (array $attributes) {
            $periodeFin = $this->faker->dateTimeBetween('-3 months', '-1 month');
            $periodeDebut = Carbon::parse($periodeFin)->subMonth()->addDay();

            return [
                'periode_debut' => $periodeDebut,
                'periode_fin' => $periodeFin,
                'statut' => 'calcule', // Calculé mais pas encore payé
                'date_paiement' => null,
            ];
        });
    }

    /**
     * État pour gros paiement (grand éleveur)
     */
    public function grosPaiement(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_totale' => $this->faker->randomFloat(2, 1000, 3000),
        ]);
    }

    /**
     * État pour petit paiement (petit éleveur)
     */
    public function petitPaiement(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_totale' => $this->faker->randomFloat(2, 100, 600),
        ]);
    }

    /**
     * État basé sur les réceptions réelles d'un membre
     */
    public function basedOnReceptions(MembreEleveur $membre, $startDate, $endDate): static
    {
        return $this->state(function (array $attributes) use ($membre, $startDate, $endDate) {
            // Calculer la quantité totale des réceptions pour cette période
            $quantiteTotale = ReceptionLait::where('id_membre', $membre->id_membre)
                                         ->whereBetween('date_reception', [$startDate, $endDate])
                                         ->sum('quantite_litres') ?? 0;

            $prix = $this->generatePrixEleveur($startDate);

            return [
                'id_membre' => $membre->id_membre,
                'id_cooperative' => $membre->id_cooperative,
                'periode_debut' => $startDate,
                'periode_fin' => $endDate,
                'quantite_totale' => $quantiteTotale,
                'prix_unitaire' => $prix,
                'montant_total' => round($quantiteTotale * $prix, 2),
            ];
        });
    }

    /**
     * Créer des paiements mensuels pour tous les membres d'une coopérative
     */
    public function monthlyForCooperative(Cooperative $cooperative, $mois, $annee): array
    {
        $paiements = [];
        $debut = Carbon::create($annee, $mois, 1);
        $fin = $debut->copy()->endOfMonth();
        
        $membres = $cooperative->membresActifs;
        
        foreach ($membres as $membre) {
            // Calculer les réceptions pour ce membre ce mois-là
            $quantite = ReceptionLait::where('id_membre', $membre->id_membre)
                                   ->whereBetween('date_reception', [$debut, $fin])
                                   ->sum('quantite_litres') ?? 0;
            
            // Créer paiement seulement si il y a eu des réceptions
            if ($quantite > 0) {
                $paiements[] = $this->forMembre($membre)
                                   ->mensuel($mois, $annee)
                                   ->state([
                                       'quantite_totale' => $quantite,
                                   ])
                                   ->make()
                                   ->toArray();
            }
        }

        return $paiements;
    }

    /**
     * État pour paiement avec délai réaliste
     */
    public function withRealisticTiming(): static
    {
        return $this->state(function (array $attributes) {
            $periodeFin = Carbon::parse($attributes['periode_fin']);
            $maintenant = Carbon::now();
            
            $joursEcoules = $periodeFin->diffInDays($maintenant);
            
            // Logique réaliste des paiements aux éleveurs
            if ($joursEcoules >= 45) {
                // Ancienne période: 90% payée
                $statut = $this->faker->boolean(90) ? 'paye' : 'calcule';
            } elseif ($joursEcoules >= 15) {
                // Période moyennement ancienne: 70% payée
                $statut = $this->faker->boolean(70) ? 'paye' : 'calcule';
            } else {
                // Période récente: 30% payée
                $statut = $this->faker->boolean(30) ? 'paye' : 'calcule';
            }

            $datePaiement = null;
            if ($statut === 'paye') {
                $datePaiement = $periodeFin->copy()->addDays($this->faker->numberBetween(5, 30));
            }

            return [
                'statut' => $statut,
                'date_paiement' => $datePaiement,
            ];
        });
    }

    /**
     * Créer des paiements trimestriels
     */
    public function quarterly($trimestre, $annee): static
    {
        return $this->state(function (array $attributes) use ($trimestre, $annee) {
            $debuts = [
                1 => Carbon::create($annee, 1, 1),  // Q1
                2 => Carbon::create($annee, 4, 1),  // Q2
                3 => Carbon::create($annee, 7, 1),  // Q3
                4 => Carbon::create($annee, 10, 1), // Q4
            ];
            
            $debut = $debuts[$trimestre];
            $fin = $debut->copy()->addMonths(3)->subDay();

            return [
                'periode_debut' => $debut,
                'periode_fin' => $fin,
                'prix_unitaire' => $this->generatePrixEleveur($debut),
            ];
        });
    }
}