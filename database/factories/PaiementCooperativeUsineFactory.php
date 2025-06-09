<?php

namespace Database\Factories;

use App\Models\PaiementCooperativeUsine;
use App\Models\Cooperative;
use App\Models\LivraisonUsine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaiementCooperativeUsine>
 */
class PaiementCooperativeUsineFactory extends Factory
{
    protected $model = PaiementCooperativeUsine::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'id_cooperative' => Cooperative::factory(),
            'id_livraison' => LivraisonUsine::factory(),
            'date_paiement' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'montant' => $this->faker->randomFloat(2, 500, 15000), // Sera généralement écrasé par la livraison
            'statut' => $this->faker->randomElement(['en_attente', 'paye']),
        ];
    }

    /**
     * État pour paiement en attente
     */
    public function enAttente(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'en_attente',
            'date_paiement' => $this->faker->dateTimeBetween('now', '+1 month'), // Date future pour paiement prévu
        ]);
    }

    /**
     * État pour paiement effectué
     */
    public function paye(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'paye',
            'date_paiement' => $this->faker->dateTimeBetween('-2 months', 'now'),
        ]);
    }

    /**
     * État pour paiement basé sur une livraison spécifique
     */
    public function forLivraison(LivraisonUsine $livraison): static
    {
        return $this->state(function (array $attributes) use ($livraison) {
            // Date de paiement basée sur la date de livraison
            $dateLivraison = Carbon::parse($livraison->date_livraison);
            $datePaiement = $dateLivraison->copy()->addDays($this->faker->numberBetween(7, 45)); // Paiement entre 1 semaine et 1.5 mois après

            // Statut basé sur la date
            $statut = $datePaiement->isPast() ? 'paye' : 'en_attente';

            return [
                'id_cooperative' => $livraison->id_cooperative,
                'id_livraison' => $livraison->id_livraison,
                'montant' => $livraison->montant_total,
                'date_paiement' => $datePaiement,
                'statut' => $statut,
            ];
        });
    }

    /**
     * État pour paiement avec coopérative spécifique
     */
    public function forCooperative(Cooperative $cooperative): static
    {
        return $this->state(fn (array $attributes) => [
            'id_cooperative' => $cooperative->id_cooperative,
        ]);
    }

    /**
     * État pour paiement récent
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_paiement' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'statut' => 'paye',
        ]);
    }

    /**
     * État pour paiement à venir
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_paiement' => $this->faker->dateTimeBetween('now', '+2 months'),
            'statut' => 'en_attente',
        ]);
    }

    /**
     * État pour paiement avec montant spécifique
     */
    public function withMontant(float $montant): static
    {
        return $this->state(fn (array $attributes) => [
            'montant' => $montant,
        ]);
    }

    /**
     * État pour paiement à une date spécifique
     */
    public function onDate($date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            $datePaiement = Carbon::parse($date);
            $statut = $datePaiement->isPast() ? 'paye' : 'en_attente';

            return [
                'date_paiement' => $date,
                'statut' => $statut,
            ];
        });
    }

    /**
     * État pour paiement ce mois
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_paiement' => $this->faker->dateTimeBetween('first day of this month', 'now'),
            'statut' => 'paye',
        ]);
    }

    /**
     * État pour paiement le mois dernier
     */
    public function lastMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_paiement' => $this->faker->dateTimeBetween('first day of last month', 'last day of last month'),
            'statut' => 'paye',
        ]);
    }

    /**
     * État pour gros paiement
     */
    public function grosPaiement(): static
    {
        return $this->state(fn (array $attributes) => [
            'montant' => $this->faker->randomFloat(2, 10000, 50000),
        ]);
    }

    /**
     * État pour petit paiement
     */
    public function petitPaiement(): static
    {
        return $this->state(fn (array $attributes) => [
            'montant' => $this->faker->randomFloat(2, 500, 5000),
        ]);
    }

    /**
     * État pour paiement en retard
     */
    public function enRetard(): static
    {
        return $this->state(function (array $attributes) {
            // Date de paiement prévue dans le passé mais statut en_attente
            $datePrevue = $this->faker->dateTimeBetween('-2 months', '-1 week');

            return [
                'date_paiement' => $datePrevue,
                'statut' => 'en_attente',
            ];
        });
    }

    /**
     * État pour paiement ponctuel
     */
    public function ponctuel(): static
    {
        return $this->state(function (array $attributes) {
            // Paiement effectué dans les délais normaux (7-30 jours après livraison)
            return [
                'statut' => 'paye',
            ];
        });
    }

    /**
     * Créer des paiements basés sur les livraisons existantes d'une coopérative
     */
    public function createFromLivraisons(Cooperative $cooperative): array
    {
        $paiements = [];
        $livraisons = $cooperative->livraisonsUsine()->where('statut', '!=', 'planifiee')->get();

        foreach ($livraisons as $livraison) {
            // 80% de chance qu'une livraison validée/payée ait un paiement associé
            if ($this->faker->boolean(80)) {
                $paiements[] = $this->forLivraison($livraison)->make()->toArray();
            }
        }

        return $paiements;
    }

    /**
     * Générer des paiements mensuels réguliers
     */
    public function monthlyPayments(Cooperative $cooperative, $startDate, $endDate): array
    {
        $paiements = [];
        $current = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            // Paiement autour du 15 de chaque mois
            $datePaiement = $current->copy()->day(15)->addDays($this->faker->numberBetween(-5, 5));
            
            // Montant basé sur les livraisons du mois précédent (simulé)
            $montant = $this->faker->randomFloat(2, 5000, 25000);

            $paiements[] = $this->forCooperative($cooperative)
                               ->onDate($datePaiement)
                               ->withMontant($montant)
                               ->make()
                               ->toArray();

            $current->addMonth();
        }

        return $paiements;
    }

    /**
     * Appliquer une logique de délai de paiement réaliste
     */
    public function withRealisticTiming(): static
    {
        return $this->state(function (array $attributes) {
            // Les paiements suivent généralement un délai de 15-45 jours
            $delaiJours = $this->faker->numberBetween(15, 45);
            
            if (isset($attributes['id_livraison'])) {
                $livraison = LivraisonUsine::find($attributes['id_livraison']);
                if ($livraison) {
                    $dateLivraison = Carbon::parse($livraison->date_livraison);
                    $datePaiement = $dateLivraison->copy()->addDays($delaiJours);
                    
                    return [
                        'date_paiement' => $datePaiement,
                        'statut' => $datePaiement->isPast() ? 'paye' : 'en_attente',
                    ];
                }
            }

            return [];
        });
    }
}