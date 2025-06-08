<?php

namespace Database\Factories;

use App\Models\Cooperative;
use App\Models\MembreEleveur;
use App\Models\ReceptionLait;
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
        // Date de réception (entre 6 mois et aujourd'hui)
        $dateReception = $this->faker->dateTimeBetween('-6 months', 'now');
        
        // Quantité variable selon la saison et le profil de l'éleveur
        $quantite = $this->generateQuantiteRealistic($dateReception);

        return [
            'id_cooperative' => Cooperative::factory(),
            'id_membre' => MembreEleveur::factory(),
            'matricule_reception' => $this->generateMatriculeReception(),
            'date_reception' => $dateReception->format('Y-m-d'),
            'quantite_litres' => $quantite,
            'created_at' => $dateReception,
            'updated_at' => $dateReception,
        ];
    }

    /**
     * Générer une quantité réaliste selon la saison et les conditions
     */
    private function generateQuantiteRealistic(\DateTime $date): float
    {
        $mois = (int) $date->format('n');
        
        // Variation saisonnière de la production laitière au Maroc
        $facteurSaisonnier = match (true) {
            // Printemps (mars-mai) : Haute production
            in_array($mois, [3, 4, 5]) => $this->faker->randomFloat(2, 1.2, 1.5),
            
            // Été (juin-août) : Production modérée à faible
            in_array($mois, [6, 7, 8]) => $this->faker->randomFloat(2, 0.7, 1.0),
            
            // Automne (septembre-novembre) : Production moyenne à bonne
            in_array($mois, [9, 10, 11]) => $this->faker->randomFloat(2, 1.0, 1.3),
            
            // Hiver (décembre-février) : Production moyenne
            default => $this->faker->randomFloat(2, 0.9, 1.2)
        };

        // Quantité de base selon le profil de l'éleveur
        $profilEleveur = $this->faker->randomElement([
            'petit' => 40,      // 20% - Petits éleveurs (5-15L/jour)
            'moyen' => 50,      // 50% - Éleveurs moyens (15-40L/jour) 
            'grand' => 10       // 30% - Grands éleveurs (40-120L/jour)
        ]);

        $quantiteBase = match ($profilEleveur) {
            'petit' => $this->faker->randomFloat(2, 5, 15),
            'moyen' => $this->faker->randomFloat(2, 15, 40),
            'grand' => $this->faker->randomFloat(2, 40, 120),
        };

        // Variation quotidienne aléatoire (±15%)
        $variationQuotidienne = $this->faker->randomFloat(2, 0.85, 1.15);

        // Calcul final
        $quantiteFinal = $quantiteBase * $facteurSaisonnier * $variationQuotidienne;

        // Arrondir à 2 décimales et s'assurer d'un minimum de 2L
        return max(2.00, round($quantiteFinal, 2));
    }

    /**
     * Générer un matricule de réception unique
     */
    private function generateMatriculeReception(): string
    {
        // Format: REC + Année + Mois + Jour + Numéro séquentiel (5 chiffres)
        $date = now();
        $prefix = 'REC' . $date->format('ymd');
        
        // Générer un numéro séquentiel unique
        $sequence = str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT);
        
        return $prefix . $sequence;
    }

    /**
     * État pour une réception de printemps (haute production)
     */
    public function printemps(): static
    {
        return $this->state(function (array $attributes) {
            $datePrintemps = $this->faker->dateTimeBetween('2024-03-01', '2024-05-31');
            return [
                'date_reception' => $datePrintemps->format('Y-m-d'),
                'quantite_litres' => $this->faker->randomFloat(2, 25, 80), // Production élevée
                'created_at' => $datePrintemps,
                'updated_at' => $datePrintemps,
            ];
        });
    }

    /**
     * État pour une réception d'été (production faible)
     */
    public function ete(): static
    {
        return $this->state(function (array $attributes) {
            $dateEte = $this->faker->dateTimeBetween('2024-06-01', '2024-08-31');
            return [
                'date_reception' => $dateEte->format('Y-m-d'),
                'quantite_litres' => $this->faker->randomFloat(2, 8, 35), // Production réduite
                'created_at' => $dateEte,
                'updated_at' => $dateEte,
            ];
        });
    }

    /**
     * État pour une réception récente
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            $dateRecente = $this->faker->dateTimeBetween('-2 weeks', 'now');
            return [
                'date_reception' => $dateRecente->format('Y-m-d'),
                'created_at' => $dateRecente,
                'updated_at' => $dateRecente,
            ];
        });
    }

    /**
     * État pour une grande quantité
     */
    public function grandeQuantite(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $this->faker->randomFloat(2, 60, 150),
        ]);
    }

    /**
     * État pour une petite quantité
     */
    public function petiteQuantite(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_litres' => $this->faker->randomFloat(2, 3, 20),
        ]);
    }

    /**
     * Configurer avec des IDs spécifiques
     */
    public function pourMembre(int $idMembre, int $idCooperative): static
    {
        return $this->state(fn (array $attributes) => [
            'id_membre' => $idMembre,
            'id_cooperative' => $idCooperative,
        ]);
    }
}