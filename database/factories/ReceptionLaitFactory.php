<?php

namespace Database\Factories;

use App\Models\ReceptionLait;
use App\Models\Cooperative;
use App\Models\MembreEleveur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReceptionLait>
 */
class ReceptionLaitFactory extends Factory
{
    protected $model = ReceptionLait::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $membre = MembreEleveur::factory()->actif();
        $dateReception = $this->faker->dateTimeBetween('-6 months', 'now');
        
        return [
            'id_cooperative' => function (array $attributes) {
                // Si id_membre est fourni, utiliser sa coopérative
                if (isset($attributes['id_membre'])) {
                    $membre = MembreEleveur::find($attributes['id_membre']);
                    return $membre ? $membre->id_cooperative : Cooperative::factory();
                }
                return Cooperative::factory();
            },
            'id_membre' => $membre,
            'matricule_reception' => '', // Sera généré automatiquement par le modèle
            'date_reception' => $dateReception,
            'quantite_litres' => $this->generateQuantiteRealiste($dateReception),
        ];
    }

    /**
     * Générer une quantité réaliste selon la saison et le type d'éleveur
     */
    private function generateQuantiteRealiste($date): float
    {
        $mois = Carbon::parse($date)->month;
        
        // Facteur saisonnier (plus de lait au printemps)
        $facteurSaisonnier = match(true) {
            in_array($mois, [3, 4, 5]) => 1.3,     // Printemps: pic de lactation
            in_array($mois, [6, 7, 8]) => 0.9,     // Été: chaleur réduit la production
            in_array($mois, [9, 10, 11]) => 1.1,   // Automne: bonne période
            default => 0.8                          // Hiver: production minimale
        };

        // Type d'éleveur (petit, moyen, grand)
        $typeEleveur = $this->faker->randomElement(['petit', 'moyen', 'grand']);
        
        $quantiteBase = match($typeEleveur) {
            'petit' => $this->faker->randomFloat(2, 5, 25),      // 5-25 litres
            'moyen' => $this->faker->randomFloat(2, 20, 60),     // 20-60 litres
            'grand' => $this->faker->randomFloat(2, 50, 150),    // 50-150 litres
        };

        // Variation quotidienne aléatoire (±20%)
        $variationQuotidienne = $this->faker->randomFloat(2, 0.8, 1.2);
        
        $quantiteFinale = $quantiteBase * $facteurSaisonnier * $variationQuotidienne;
        
        return round($quantiteFinale, 2);
    }

    /**
     * État pour réception d'un petit éleveur
     */
    public function petitEleveur(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $this->faker->randomFloat(2, 5, 25),
        ]);
    }

    /**
     * État pour réception d'un moyen éleveur
     */
    public function moyenEleveur(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $this->faker->randomFloat(2, 20, 60),
        ]);
    }

    /**
     * État pour réception d'un grand éleveur
     */
    public function grandEleveur(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $this->faker->randomFloat(2, 50, 150),
        ]);
    }

    /**
     * État pour une réception à une date spécifique
     */
    public function onDate($date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'date_reception' => $date,
                'quantite_litres' => $this->generateQuantiteRealiste($date),
            ];
        });
    }

    /**
     * État pour réception récente (dernière semaine)
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('-1 week', 'now');
            return [
                'date_reception' => $date,
                'quantite_litres' => $this->generateQuantiteRealiste($date),
            ];
        });
    }

    /**
     * État pour réception aujourd'hui
     */
    public function today(): static
    {
        return $this->state(function (array $attributes) {
            $date = today();
            return [
                'date_reception' => $date,
                'quantite_litres' => $this->generateQuantiteRealiste($date),
            ];
        });
    }

    /**
     * État pour réception cette semaine
     */
    public function thisWeek(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('monday this week', 'now');
            return [
                'date_reception' => $date,
                'quantite_litres' => $this->generateQuantiteRealiste($date),
            ];
        });
    }

    /**
     * État pour réception ce mois
     */
    public function thisMonth(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('first day of this month', 'now');
            return [
                'date_reception' => $date,
                'quantite_litres' => $this->generateQuantiteRealiste($date),
            ];
        });
    }

    /**
     * État pour réception en période de pic (printemps)
     */
    public function picProduction(): static
    {
        return $this->state(function (array $attributes) {
            // Mars, Avril, Mai de l'année en cours ou précédente
            $annee = $this->faker->randomElement([date('Y'), date('Y') - 1]);
            $mois = $this->faker->randomElement([3, 4, 5]);
            $jour = $this->faker->numberBetween(1, 28);
            
            $date = Carbon::create($annee, $mois, $jour);
            
            return [
                'date_reception' => $date,
                'quantite_litres' => $this->generateQuantiteRealiste($date),
            ];
        });
    }

    /**
     * État pour réception en période faible (hiver)
     */
    public function faibleProduction(): static
    {
        return $this->state(function (array $attributes) {
            // Décembre, Janvier, Février
            $annee = $this->faker->randomElement([date('Y'), date('Y') - 1]);
            $mois = $this->faker->randomElement([12, 1, 2]);
            $jour = $this->faker->numberBetween(1, 28);
            
            $date = Carbon::create($annee, $mois, $jour);
            
            return [
                'date_reception' => $date,
                'quantite_litres' => $this->generateQuantiteRealiste($date),
            ];
        });
    }

    /**
     * État pour réception avec membre spécifique
     */
    public function forMembre(MembreEleveur $membre): static
    {
        return $this->state(fn (array $attributes) => [
            'id_membre' => $membre->id_membre,
            'id_cooperative' => $membre->id_cooperative,
        ]);
    }

    /**
     * État pour réception avec coopérative spécifique
     */
    public function forCooperative(Cooperative $cooperative): static
    {
        return $this->state(function (array $attributes) use ($cooperative) {
            // Sélectionner un membre aléatoire de cette coopérative
            $membre = $cooperative->membresActifs()->inRandomOrder()->first();
            
            if (!$membre) {
                // Si pas de membre actif, en créer un
                $membre = MembreEleveur::factory()->actif()->forCooperative($cooperative)->create();
            }

            return [
                'id_cooperative' => $cooperative->id_cooperative,
                'id_membre' => $membre->id_membre,
            ];
        });
    }

    /**
     * État pour réception avec quantité spécifique
     */
    public function withQuantite(float $quantite): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $quantite,
        ]);
    }

    /**
     * Créer des réceptions quotidiennes pour un membre sur une période
     */
    public function dailyForPeriod(MembreEleveur $membre, $startDate, $endDate): array
    {
        $receptions = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            // Pas de réception le dimanche (jour de repos)
            if ($current->dayOfWeek !== Carbon::SUNDAY) {
                // Probabilité de 85% d'avoir une réception un jour donné
                if ($this->faker->boolean(85)) {
                    $receptions[] = $this->forMembre($membre)
                                        ->onDate($current->toDateString())
                                        ->make()
                                        ->toArray();
                }
            }
            $current->addDay();
        }

        return $receptions;
    }
}