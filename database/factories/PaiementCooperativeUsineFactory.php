<?php

namespace Database\Factories;

use App\Models\Cooperative;
use App\Models\LivraisonUsine;
use App\Models\PaiementCooperativeUsine;
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
        $datePaiement = $this->faker->dateTimeBetween('-3 months', 'now');

        return [
            'id_cooperative' => Cooperative::factory(),
            'id_livraison' => LivraisonUsine::factory(),
            'date_paiement' => $datePaiement->format('Y-m-d'),
            'montant' => $this->faker->randomFloat(2, 1000, 15000), // Montant réaliste
            'statut' => $this->faker->randomElement([
                'en_attente' => 30,  // 30% en attente
                'paye' => 70         // 70% payé
            ]),
            'created_at' => $datePaiement,
            'updated_at' => function (array $attributes) use ($datePaiement) {
                // Mise à jour peut être après la création si statut change
                if ($attributes['statut'] === 'paye') {
                    return $this->faker->dateTimeBetween($datePaiement, 'now');
                }
                return $datePaiement;
            },
        ];
    }

    /**
     * Basé sur une livraison existante
     */
    public function baseSurLivraison(LivraisonUsine $livraison): static
    {
        return $this->state(function (array $attributes) use ($livraison) {
            // Date de paiement basée sur la date de livraison
            $dateLivraison = Carbon::parse($livraison->date_livraison);
            $delaiPaiement = $this->calculerDelaiPaiement($livraison);
            $datePaiement = $dateLivraison->copy()->addDays($delaiPaiement);

            // Statut selon l'ancienneté
            $statut = $this->determinerStatutSelonDate($datePaiement);

            // Montant peut être légèrement différent (frais, bonus, etc.)
            $montant = $this->ajusterMontant($livraison->montant_total);

            return [
                'id_cooperative' => $livraison->id_cooperative,
                'id_livraison' => $livraison->id_livraison,
                'date_paiement' => $datePaiement->format('Y-m-d'),
                'montant' => $montant,
                'statut' => $statut,
                'created_at' => $dateLivraison->copy()->addDays(rand(1, 5)), // Création quelques jours après livraison
                'updated_at' => $statut === 'paye' ? $datePaiement : $dateLivraison->copy()->addDays(rand(1, 5)),
            ];
        });
    }

    /**
     * Calculer le délai de paiement réaliste
     */
    private function calculerDelaiPaiement(LivraisonUsine $livraison): int
    {
        // Délai selon la taille de la coopérative et historique
        $cooperative = $livraison->cooperative;
        $nombreMembres = $cooperative->membres()->where('statut', 'actif')->count();

        return match (true) {
            // Grandes coopératives : délais plus courts (partenariat privilégié)
            $nombreMembres >= 60 => rand(10, 25),
            
            // Moyennes coopératives : délais standards
            $nombreMembres >= 25 => rand(20, 35),
            
            // Petites coopératives : délais plus longs
            default => rand(30, 50)
        };
    }

    /**
     * Déterminer le statut selon la date
     */
    private function determinerStatutSelonDate(Carbon $datePaiement): string
    {
        $joursDepuis = $datePaiement->diffInDays(now());
        
        return match (true) {
            // Si la date de paiement est dans le futur ou très récente
            $joursDepuis <= 7 => 'en_attente',
            
            // Si c'est dans le passé, plus probable que ce soit payé
            default => $this->faker->randomElement([
                'paye' => 85,        // 85% de chance d'être payé
                'en_attente' => 15   // 15% encore en attente
            ])
        };
    }

    /**
     * Ajuster le montant par rapport à la livraison
     */
    private function ajusterMontant(float $montantLivraison): float
    {
        // Facteurs possibles
        $facteurs = [
            1.0,    // 70% - Montant identique
            0.98,   // 10% - Déduction frais (-2%)
            0.95,   // 5% - Déduction pénalité (-5%)
            1.02,   // 10% - Bonus qualité (+2%)
            1.05,   // 5% - Prime performance (+5%)
        ];

        $facteur = $this->faker->randomElement($facteurs);
        return round($montantLivraison * $facteur, 2);
    }

    /**
     * État pour un paiement en attente
     */
    public function enAttente(): static
    {
        return $this->state(function (array $attributes) {
            // Date de paiement dans le futur
            $datePaiement = $this->faker->dateTimeBetween('now', '+2 months');
            
            return [
                'date_paiement' => $datePaiement->format('Y-m-d'),
                'statut' => 'en_attente',
                'updated_at' => $attributes['created_at'] ?? now(),
            ];
        });
    }

    /**
     * État pour un paiement effectué
     */
    public function paye(): static
    {
        return $this->state(function (array $attributes) {
            $datePaiement = Carbon::parse($attributes['date_paiement'] ?? now()->subDays(15));
            
            return [
                'statut' => 'paye',
                'updated_at' => $datePaiement->copy()->addHours(rand(1, 24)), // Mise à jour le jour du paiement
            ];
        });
    }

    /**
     * État pour un paiement en retard
     */
    public function enRetard(): static
    {
        return $this->state(function (array $attributes) {
            // Date de paiement dans le passé mais statut en attente
            $datePaiement = $this->faker->dateTimeBetween('-2 months', '-1 week');
            
            return [
                'date_paiement' => $datePaiement->format('Y-m-d'),
                'statut' => 'en_attente',
                'updated_at' => $attributes['created_at'] ?? $datePaiement,
            ];
        });
    }

    /**
     * État pour un gros montant
     */
    public function grosMontant(): static
    {
        return $this->state(fn (array $attributes) => [
            'montant' => $this->faker->randomFloat(2, 8000, 25000),
        ]);
    }

    /**
     * État pour un petit montant
     */
    public function petitMontant(): static
    {
        return $this->state(fn (array $attributes) => [
            'montant' => $this->faker->randomFloat(2, 500, 3000),
        ]);
    }

    /**
     * État pour un paiement récent
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            $dateRecente = $this->faker->dateTimeBetween('-2 weeks', 'now');
            
            return [
                'date_paiement' => $dateRecente->format('Y-m-d'),
                'created_at' => $dateRecente,
                'updated_at' => $dateRecente,
            ];
        });
    }

    /**
     * État avec bonus de qualité
     */
    public function avecBonus(): static
    {
        return $this->state(function (array $attributes) {
            $montantBase = $attributes['montant'] ?? 5000;
            $bonus = $montantBase * 0.05; // Bonus de 5%
            
            return [
                'montant' => round($montantBase + $bonus, 2),
            ];
        });
    }

    /**
     * État avec déduction
     */
    public function avecDeduction(): static
    {
        return $this->state(function (array $attributes) {
            $montantBase = $attributes['montant'] ?? 5000;
            $deduction = $montantBase * 0.03; // Déduction de 3%
            
            return [
                'montant' => round($montantBase - $deduction, 2),
            ];
        });
    }

    /**
     * Configurer pour une coopérative spécifique
     */
    public function pourCooperative(int $idCooperative): static
    {
        return $this->state(fn (array $attributes) => [
            'id_cooperative' => $idCooperative,
        ]);
    }

    /**
     * Configurer pour une livraison spécifique
     */
    public function pourLivraison(int $idLivraison): static
    {
        return $this->state(fn (array $attributes) => [
            'id_livraison' => $idLivraison,
        ]);
    }

    /**
     * Configurer pour une date spécifique
     */
    public function pourDate(string $date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            $dateCarbon = Carbon::parse($date);
            
            return [
                'date_paiement' => $date,
                'created_at' => $dateCarbon->copy()->subDays(rand(1, 10)),
                'updated_at' => $dateCarbon,
            ];
        });
    }
}