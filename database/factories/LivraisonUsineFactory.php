<?php

namespace Database\Factories;

use App\Models\LivraisonUsine;
use App\Models\Cooperative;
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
        $prixUnitaire = $this->generatePrixUnitaire($dateLivraison);
        
        return [
            'id_cooperative' => Cooperative::factory(),
            'date_livraison' => $dateLivraison,
            'quantite_litres' => $this->generateQuantiteLivraison(),
            'prix_unitaire' => $prixUnitaire,
            'montant_total' => 0, // Sera calculé automatiquement par le modèle
            'statut' => $this->faker->randomElement(['planifiee', 'validee', 'payee']),
        ];
    }

    /**
     * Générer un prix unitaire réaliste selon le marché marocain
     */
    private function generatePrixUnitaire($date): float
    {
        $mois = Carbon::parse($date)->month;
        
        // Prix de base du lait au Maroc (en DH/litre)
        $prixBase = 4.20; // Prix moyen au Maroc
        
        // Variation saisonnière
        $variationSaisonniere = match(true) {
            in_array($mois, [3, 4, 5]) => 0.95,    // Printemps: plus d'offre, prix plus bas
            in_array($mois, [6, 7, 8]) => 1.10,    // Été: moins d'offre, prix plus élevé
            in_array($mois, [9, 10, 11]) => 1.00,  // Automne: prix stable
            default => 1.05                         // Hiver: prix légèrement élevé
        };
        
        // Variation de marché (±5%)
        $variationMarche = $this->faker->randomFloat(2, 0.95, 1.05);
        
        $prix = $prixBase * $variationSaisonniere * $variationMarche;
        
        return round($prix, 2);
    }

    /**
     * Générer une quantité de livraison réaliste
     */
    private function generateQuantiteLivraison(): float
    {
        // Les livraisons sont généralement importantes (collecte de plusieurs jours)
        $types = ['petite', 'moyenne', 'grande'];
        $type = $this->faker->randomElement($types);
        
        $quantite = match($type) {
            'petite' => $this->faker->randomFloat(2, 100, 500),      // 100-500L
            'moyenne' => $this->faker->randomFloat(2, 500, 1500),    // 500-1500L
            'grande' => $this->faker->randomFloat(2, 1500, 5000),    // 1500-5000L
        };
        
        return round($quantite, 2);
    }

    /**
     * État pour livraison planifiée
     */
    public function planifiee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'planifiee',
        ]);
    }

    /**
     * État pour livraison validée
     */
    public function validee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'validee',
        ]);
    }

    /**
     * État pour livraison payée
     */
    public function payee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'payee',
        ]);
    }

    /**
     * État pour livraison à une date spécifique
     */
    public function onDate($date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'date_livraison' => $date,
                'prix_unitaire' => $this->generatePrixUnitaire($date),
            ];
        });
    }

    /**
     * État pour livraison récente
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('-1 week', 'now');
            return [
                'date_livraison' => $date,
                'prix_unitaire' => $this->generatePrixUnitaire($date),
            ];
        });
    }

    /**
     * État pour livraison cette semaine
     */
    public function thisWeek(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('monday this week', 'now');
            return [
                'date_livraison' => $date,
                'prix_unitaire' => $this->generatePrixUnitaire($date),
            ];
        });
    }

    /**
     * État pour livraison ce mois
     */
    public function thisMonth(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('first day of this month', 'now');
            return [
                'date_livraison' => $date,
                'prix_unitaire' => $this->generatePrixUnitaire($date),
            ];
        });
    }

    /**
     * État pour petite livraison
     */
    public function petite(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $this->faker->randomFloat(2, 100, 500),
        ]);
    }

    /**
     * État pour moyenne livraison
     */
    public function moyenne(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $this->faker->randomFloat(2, 500, 1500),
        ]);
    }

    /**
     * État pour grande livraison
     */
    public function grande(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $this->faker->randomFloat(2, 1500, 5000),
        ]);
    }

    /**
     * État pour livraison avec coopérative spécifique
     */
    public function forCooperative(Cooperative $cooperative): static
    {
        return $this->state(fn (array $attributes) => [
            'id_cooperative' => $cooperative->id_cooperative,
        ]);
    }

    /**
     * État pour livraison avec quantité spécifique
     */
    public function withQuantite(float $quantite): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $quantite,
        ]);
    }

    /**
     * État pour livraison avec prix spécifique
     */
    public function withPrix(float $prix): static
    {
        return $this->state(fn (array $attributes) => [
            'prix_unitaire' => $prix,
        ]);
    }

    /**
     * État pour livraison en période de haute saison
     */
    public function hauteSaison(): static
    {
        return $this->state(function (array $attributes) {
            // Été: prix plus élevés
            $annee = $this->faker->randomElement([date('Y'), date('Y') - 1]);
            $mois = $this->faker->randomElement([6, 7, 8]);
            $jour = $this->faker->numberBetween(1, 28);
            
            $date = Carbon::create($annee, $mois, $jour);
            
            return [
                'date_livraison' => $date,
                'prix_unitaire' => $this->generatePrixUnitaire($date),
            ];
        });
    }

    /**
     * État pour livraison en période de basse saison
     */
    public function basseSaison(): static
    {
        return $this->state(function (array $attributes) {
            // Printemps: prix plus bas
            $annee = $this->faker->randomElement([date('Y'), date('Y') - 1]);
            $mois = $this->faker->randomElement([3, 4, 5]);
            $jour = $this->faker->numberBetween(1, 28);
            
            $date = Carbon::create($annee, $mois, $jour);
            
            return [
                'date_livraison' => $date,
                'prix_unitaire' => $this->generatePrixUnitaire($date),
            ];
        });
    }

    /**
     * État pour livraison basée sur le stock disponible
     */
    public function basedOnStock(Cooperative $cooperative, $date): static
    {
        return $this->state(function (array $attributes) use ($cooperative, $date) {
            $stock = StockLait::where('id_cooperative', $cooperative->id_cooperative)
                              ->whereDate('date_stock', $date)
                              ->first();
            
            if ($stock && $stock->quantite_disponible > 0) {
                // Livrer entre 70% et 100% du stock disponible
                $pourcentage = $this->faker->randomFloat(2, 0.7, 1.0);
                $quantite = round($stock->quantite_disponible * $pourcentage, 2);
            } else {
                // Stock par défaut si pas de stock trouvé
                $quantite = $this->faker->randomFloat(2, 100, 1000);
            }

            return [
                'id_cooperative' => $cooperative->id_cooperative,
                'date_livraison' => $date,
                'quantite_litres' => $quantite,
                'prix_unitaire' => $this->generatePrixUnitaire($date),
            ];
        });
    }

    /**
     * Créer livraisons hebdomadaires pour une coopérative sur une période
     */
    public function weeklyForPeriod(Cooperative $cooperative, $startDate, $endDate): array
    {
        $livraisons = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($current <= $end) {
            // Livraisons les mardi et vendredi généralement
            if (in_array($current->dayOfWeek, [Carbon::TUESDAY, Carbon::FRIDAY])) {
                // 90% de chance d'avoir une livraison
                if ($this->faker->boolean(90)) {
                    $livraisons[] = $this->forCooperative($cooperative)
                                        ->onDate($current->toDateString())
                                        ->make()
                                        ->toArray();
                }
            }
            $current->addDay();
        }

        return $livraisons;
    }

    /**
     * Créer une progression de statuts réaliste
     */
    public function withProgressiveStatus(): static
    {
        return $this->state(function (array $attributes) {
            $dateLivraison = Carbon::parse($attributes['date_livraison']);
            $maintenant = Carbon::now();
            
            $joursEcoules = $dateLivraison->diffInDays($maintenant);
            
            // Logique de progression des statuts
            if ($joursEcoules >= 30) {
                $statut = 'payee'; // Livraisons anciennes sont généralement payées
            } elseif ($joursEcoules >= 7) {
                $statut = $this->faker->randomElement(['validee', 'payee']);
            } elseif ($joursEcoules >= 2) {
                $statut = $this->faker->randomElement(['planifiee', 'validee']);
            } else {
                $statut = 'planifiee'; // Livraisons récentes encore en attente
            }

            return [
                'statut' => $statut,
            ];
        });
    }
}