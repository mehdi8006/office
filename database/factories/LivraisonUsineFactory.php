<?php

namespace Database\Factories;

use App\Models\Cooperative;
use App\Models\LivraisonUsine;
use App\Models\StockLait;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LivraisonUsine>
 */
class LivraisonUsineFactory extends Factory
{
    protected $model = LivraisonUsine::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $dateLivraison = $this->faker->dateTimeBetween('-6 months', 'now');
        
        // Quantité livrée réaliste
        $quantiteLitres = $this->faker->randomFloat(2, 200, 2500);
        
        // Prix unitaire réaliste au Maroc (4-6 DH/litre)
        $prixUnitaire = $this->generatePrixRealistic($dateLivraison);
        
        // Calcul du montant total
        $montantTotal = round($quantiteLitres * $prixUnitaire, 2);

        return [
            'id_cooperative' => Cooperative::factory(),
            'date_livraison' => $dateLivraison->format('Y-m-d'),
            'quantite_litres' => $quantiteLitres,
            'prix_unitaire' => $prixUnitaire,
            'montant_total' => $montantTotal,
            'statut' => $this->faker->randomElement([
                'planifiee' => 15,    // 15% planifiées
                'validee' => 60,      // 60% validées
                'payee' => 25         // 25% payées
            ]),
            'created_at' => $dateLivraison,
            'updated_at' => function (array $attributes) use ($dateLivraison) {
                // La mise à jour peut être quelques jours après la création
                return $this->faker->dateTimeBetween($dateLivraison, 'now');
            },
        ];
    }

    /**
     * Générer un prix réaliste selon la période et la qualité
     */
    private function generatePrixRealistic(\DateTime $date): float
    {
        $mois = (int) $date->format('n');
        
        // Prix de base selon la saison (variation offre/demande)
        $prixBase = match (true) {
            // Printemps : Forte production, prix plus bas
            in_array($mois, [3, 4, 5]) => $this->faker->randomFloat(2, 4.0, 4.8),
            
            // Été : Production réduite, prix plus élevé
            in_array($mois, [6, 7, 8]) => $this->faker->randomFloat(2, 5.2, 6.0),
            
            // Automne : Production moyenne, prix stable
            in_array($mois, [9, 10, 11]) => $this->faker->randomFloat(2, 4.5, 5.5),
            
            // Hiver : Production normale, prix moyen
            default => $this->faker->randomFloat(2, 4.8, 5.3)
        };

        // Facteur qualité (prime/malus selon la coopérative)
        $facteurQualite = $this->faker->randomElement([
            1.0,    // 60% - Prix standard
            1.05,   // 25% - Prime qualité (+5%)
            1.1,    // 10% - Excellente qualité (+10%)
            0.95,   // 5% - Qualité inférieure (-5%)
        ]);

        // Variation quotidienne mineure (fluctuations du marché)
        $variationMarche = $this->faker->randomFloat(2, 0.98, 1.02);

        $prixFinal = $prixBase * $facteurQualite * $variationMarche;
        
        return round($prixFinal, 2);
    }

    /**
     * Basé sur un stock existant
     */
    public function baseSurStock(int $idCooperative, string $dateLivraison): static
    {
        return $this->state(function (array $attributes) use ($idCooperative, $dateLivraison) {
            // Rechercher le stock correspondant
            $stock = StockLait::where('id_cooperative', $idCooperative)
                ->where('date_stock', $dateLivraison)
                ->first();

            if (!$stock || $stock->quantite_livree <= 0) {
                // Si pas de stock ou pas de livraison, créer une livraison minimale
                $quantiteLitres = $this->faker->randomFloat(2, 50, 200);
            } else {
                // Utiliser la quantité du stock avec une petite variation
                $quantiteLitres = round($stock->quantite_livree * $this->faker->randomFloat(2, 0.95, 1.05), 2);
            }

            $prixUnitaire = $this->generatePrixRealistic(Carbon::parse($dateLivraison));
            
            return [
                'id_cooperative' => $idCooperative,
                'date_livraison' => $dateLivraison,
                'quantite_litres' => $quantiteLitres,
                'prix_unitaire' => $prixUnitaire,
                'montant_total' => round($quantiteLitres * $prixUnitaire, 2),
            ];
        });
    }

    /**
     * État pour une livraison planifiée
     */
    public function planifiee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'planifiee',
        ]);
    }

    /**
     * État pour une livraison validée
     */
    public function validee(): static
    {
        return $this->state(function (array $attributes) {
            $dateLivraison = Carbon::parse($attributes['date_livraison'] ?? now());
            
            return [
                'statut' => 'validee',
                // Validation généralement 1-3 jours après la livraison
                'updated_at' => $dateLivraison->copy()->addDays(rand(1, 3)),
            ];
        });
    }

    /**
     * État pour une livraison payée
     */
    public function payee(): static
    {
        return $this->state(function (array $attributes) {
            $dateLivraison = Carbon::parse($attributes['date_livraison'] ?? now());
            
            return [
                'statut' => 'payee',
                // Paiement généralement 15-45 jours après la livraison
                'updated_at' => $dateLivraison->copy()->addDays(rand(15, 45)),
            ];
        });
    }

    /**
     * État pour une grosse livraison
     */
    public function grosseLivraison(): static
    {
        return $this->state(function (array $attributes) {
            $quantiteLitres = $this->faker->randomFloat(2, 1500, 3500);
            $prixUnitaire = $attributes['prix_unitaire'] ?? 5.0;
            
            return [
                'quantite_litres' => $quantiteLitres,
                'montant_total' => round($quantiteLitres * $prixUnitaire, 2),
            ];
        });
    }

    /**
     * État pour une petite livraison
     */
    public function petiteLivraison(): static
    {
        return $this->state(function (array $attributes) {
            $quantiteLitres = $this->faker->randomFloat(2, 100, 600);
            $prixUnitaire = $attributes['prix_unitaire'] ?? 5.0;
            
            return [
                'quantite_litres' => $quantiteLitres,
                'montant_total' => round($quantiteLitres * $prixUnitaire, 2),
            ];
        });
    }

    /**
     * État pour une livraison récente
     */
    public function recente(): static
    {
        return $this->state(function (array $attributes) {
            $dateRecente = $this->faker->dateTimeBetween('-2 weeks', 'now');
            
            return [
                'date_livraison' => $dateRecente->format('Y-m-d'),
                'created_at' => $dateRecente,
                'updated_at' => $dateRecente,
            ];
        });
    }

    /**
     * État pour une livraison avec prix premium
     */
    public function prixPremium(): static
    {
        return $this->state(function (array $attributes) {
            $prixPremium = $this->faker->randomFloat(2, 5.5, 6.5); // Prix élevé
            $quantiteLitres = $attributes['quantite_litres'] ?? $this->faker->randomFloat(2, 200, 1500);
            
            return [
                'prix_unitaire' => $prixPremium,
                'montant_total' => round($quantiteLitres * $prixPremium, 2),
            ];
        });
    }

    /**
     * État pour une livraison avec prix bas
     */
    public function prixBas(): static
    {
        return $this->state(function (array $attributes) {
            $prixBas = $this->faker->randomFloat(2, 3.8, 4.5); // Prix bas
            $quantiteLitres = $attributes['quantite_litres'] ?? $this->faker->randomFloat(2, 200, 1500);
            
            return [
                'prix_unitaire' => $prixBas,
                'montant_total' => round($quantiteLitres * $prixBas, 2),
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
     * Configurer pour une date spécifique
     */
    public function pourDate(string $date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            $dateCarbon = Carbon::parse($date);
            
            return [
                'date_livraison' => $date,
                'created_at' => $dateCarbon,
                'updated_at' => $dateCarbon,
            ];
        });
    }
}