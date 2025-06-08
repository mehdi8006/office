<?php

namespace Database\Factories;

use App\Models\Cooperative;
use App\Models\StockLait;
use App\Models\ReceptionLait;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockLait>
 */
class StockLaitFactory extends Factory
{
    protected $model = StockLait::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $dateStock = $this->faker->dateTimeBetween('-6 months', 'now');
        
        // Quantité totale réaliste pour une coopérative par jour
        $quantiteTotale = $this->faker->randomFloat(2, 200, 2000);
        
        // Calcul des quantités disponibles et livrées
        $quantiteLivree = $this->calculateQuantiteLivree($quantiteTotale);
        $quantiteDisponible = max(0, $quantiteTotale - $quantiteLivree);

        return [
            'id_cooperative' => Cooperative::factory(),
            'date_stock' => $dateStock->format('Y-m-d'),
            'quantite_totale' => $quantiteTotale,
            'quantite_disponible' => $quantiteDisponible,
            'quantite_livree' => $quantiteLivree,
            'created_at' => $dateStock,
            'updated_at' => $dateStock,
        ];
    }

    /**
     * Calculer la quantité livrée de manière réaliste
     */
    private function calculateQuantiteLivree(float $quantiteTotale): float
    {
        // Stratégie de livraison basée sur la quantité totale
        if ($quantiteTotale < 300) {
            // Petites coopératives : livrent moins souvent (60-80% du stock)
            $pourcentageLivre = $this->faker->randomFloat(2, 0.6, 0.8);
        } elseif ($quantiteTotale < 800) {
            // Moyennes coopératives : livraison plus régulière (70-90%)
            $pourcentageLivre = $this->faker->randomFloat(2, 0.7, 0.9);
        } else {
            // Grandes coopératives : livraison optimisée (80-95%)
            $pourcentageLivre = $this->faker->randomFloat(2, 0.8, 0.95);
        }

        return round($quantiteTotale * $pourcentageLivre, 2);
    }

    /**
     * Calculer le stock basé sur les réceptions réelles d'une coopérative
     */
    public function baseSurReceptions(int $idCooperative, string $dateStock): static
    {
        return $this->state(function (array $attributes) use ($idCooperative, $dateStock) {
            // Calculer la quantité totale reçue ce jour-là
            $quantiteTotale = ReceptionLait::where('id_cooperative', $idCooperative)
                ->where('date_reception', $dateStock)
                ->sum('quantite_litres');

            // Si aucune réception, créer un stock vide
            if ($quantiteTotale == 0) {
                return [
                    'id_cooperative' => $idCooperative,
                    'date_stock' => $dateStock,
                    'quantite_totale' => 0,
                    'quantite_disponible' => 0,
                    'quantite_livree' => 0,
                ];
            }

            // Calculer les quantités livrées et disponibles
            $quantiteLivree = $this->calculateQuantiteLivree($quantiteTotale);
            $quantiteDisponible = max(0, $quantiteTotale - $quantiteLivree);

            return [
                'id_cooperative' => $idCooperative,
                'date_stock' => $dateStock,
                'quantite_totale' => round($quantiteTotale, 2),
                'quantite_disponible' => $quantiteDisponible,
                'quantite_livree' => $quantiteLivree,
            ];
        });
    }

    /**
     * État pour un stock avec livraison complète
     */
    public function livraisonComplete(): static
    {
        return $this->state(function (array $attributes) {
            $quantiteTotale = $attributes['quantite_totale'] ?? $this->faker->randomFloat(2, 200, 1500);
            
            return [
                'quantite_totale' => $quantiteTotale,
                'quantite_livree' => $quantiteTotale,
                'quantite_disponible' => 0,
            ];
        });
    }

    /**
     * État pour un stock sans livraison
     */
    public function sansLivraison(): static
    {
        return $this->state(function (array $attributes) {
            $quantiteTotale = $attributes['quantite_totale'] ?? $this->faker->randomFloat(2, 100, 800);
            
            return [
                'quantite_totale' => $quantiteTotale,
                'quantite_livree' => 0,
                'quantite_disponible' => $quantiteTotale,
            ];
        });
    }

    /**
     * État pour un stock avec livraison partielle
     */
    public function livraisonPartielle(): static
    {
        return $this->state(function (array $attributes) {
            $quantiteTotale = $attributes['quantite_totale'] ?? $this->faker->randomFloat(2, 300, 1200);
            $quantiteLivree = round($quantiteTotale * $this->faker->randomFloat(2, 0.3, 0.7), 2);
            
            return [
                'quantite_totale' => $quantiteTotale,
                'quantite_livree' => $quantiteLivree,
                'quantite_disponible' => $quantiteTotale - $quantiteLivree,
            ];
        });
    }

    /**
     * État pour un jour de forte production
     */
    public function forteProduction(): static
    {
        return $this->state(function (array $attributes) {
            $quantiteTotale = $this->faker->randomFloat(2, 1200, 3000);
            $quantiteLivree = $this->calculateQuantiteLivree($quantiteTotale);
            
            return [
                'quantite_totale' => $quantiteTotale,
                'quantite_livree' => $quantiteLivree,
                'quantite_disponible' => max(0, $quantiteTotale - $quantiteLivree),
            ];
        });
    }

    /**
     * État pour un jour de faible production
     */
    public function faibleProduction(): static
    {
        return $this->state(function (array $attributes) {
            $quantiteTotale = $this->faker->randomFloat(2, 50, 300);
            $quantiteLivree = $this->calculateQuantiteLivree($quantiteTotale);
            
            return [
                'quantite_totale' => $quantiteTotale,
                'quantite_livree' => $quantiteLivree,
                'quantite_disponible' => max(0, $quantiteTotale - $quantiteLivree),
            ];
        });
    }

    /**
     * État pour une date spécifique
     */
    public function pourDate(string $date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            $dateCarbon = Carbon::parse($date);
            
            return [
                'date_stock' => $date,
                'created_at' => $dateCarbon,
                'updated_at' => $dateCarbon,
            ];
        });
    }

    /**
     * État pour une coopérative spécifique
     */
    public function pourCooperative(int $idCooperative): static
    {
        return $this->state(fn (array $attributes) => [
            'id_cooperative' => $idCooperative,
        ]);
    }

    /**
     * Stock avec report du jour précédent
     */
    public function avecReport(float $quantiteReport = null): static
    {
        return $this->state(function (array $attributes) use ($quantiteReport) {
            $quantiteRecue = $this->faker->randomFloat(2, 200, 1000);
            $report = $quantiteReport ?? $this->faker->randomFloat(2, 0, 200);
            $quantiteTotale = $quantiteRecue + $report;
            
            $quantiteLivree = $this->calculateQuantiteLivree($quantiteTotale);
            
            return [
                'quantite_totale' => $quantiteTotale,
                'quantite_livree' => $quantiteLivree,
                'quantite_disponible' => max(0, $quantiteTotale - $quantiteLivree),
            ];
        });
    }
}