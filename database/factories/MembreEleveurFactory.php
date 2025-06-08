<?php

namespace Database\Factories;

use App\Models\Cooperative;
use App\Models\MembreEleveur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembreEleveur>
 */
class MembreEleveurFactory extends Factory
{
    protected $model = MembreEleveur::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Noms marocains typiques
        $prenoms = ['Ahmed', 'Mohammed', 'Hassan', 'Youssef', 'Ali', 'Omar', 'Khalid', 'Abderrahim', 'Mustapha', 'Said',
                   'Fatima', 'Aicha', 'Khadija', 'Meryem', 'Zineb', 'Hafsa', 'Salma', 'Nadia', 'Leila', 'Amina'];
        
        $noms = ['Alami', 'Benali', 'Chraibi', 'El Fassi', 'Idrissi', 'Jabri', 'Kettani', 'Lazrak', 'Mahjoub', 'Naciri',
                'Ouali', 'Qadiri', 'Rachidi', 'Sabri', 'Tazi', 'Wahbi', 'Yamani', 'Zahra', 'Benjelloun', 'Berrada'];

        // Villes et régions marocaines
        $villes = [
            'Casablanca', 'Rabat', 'Fès', 'Marrakech', 'Agadir', 'Tanger', 'Meknès', 'Oujda', 
            'Kénitra', 'Tétouan', 'Safi', 'Mohammedia', 'Khouribga', 'Beni Mellal', 'El Jadida',
            'Nador', 'Taza', 'Settat', 'Berrechid', 'Khemisset'
        ];

        $prenom = $this->faker->randomElement($prenoms);
        $nom = $this->faker->randomElement($noms);
        $nomComplet = "$prenom $nom";
        
        $ville = $this->faker->randomElement($villes);
        $adresse = $this->faker->numberBetween(1, 999) . " " . 
                  $this->faker->randomElement(['Rue', 'Avenue', 'Boulevard', 'Impasse']) . " " .
                  $this->faker->randomElement(['Hassan II', 'Mohammed V', 'des FAR', 'Al Massira', 'Al Andalous', 'Al Qods']) . 
                  ", $ville";

        // Génération du numéro de carte nationale marocaine (format: lettres + chiffres)
        $carteNationale = strtoupper($this->faker->lexify('??')) . $this->faker->numerify('######');

        // Email basé sur le nom
        $emailBase = strtolower(str_replace(' ', '.', $nomComplet));
        $emailBase = $this->removeAccents($emailBase);

        return [
            'id_cooperative' => Cooperative::factory(),
            'nom_complet' => $nomComplet,
            'adresse' => $adresse,
            'telephone' => $this->generateMoroccanPhone(),
            'email' => $emailBase . '@' . $this->faker->randomElement(['gmail.com', 'yahoo.fr', 'hotmail.com', 'outlook.com']),
            'numero_carte_nationale' => $carteNationale,
            'statut' => $this->faker->randomElement([
                'actif' => 85,      // 85% actifs
                'inactif' => 10,    // 10% inactifs  
                'suppression' => 5  // 5% en suppression
            ]),
            'raison_suppression' => function (array $attributes) {
                if ($attributes['statut'] === 'suppression') {
                    return $this->faker->randomElement([
                        'Arrêt d\'activité',
                        'Déménagement hors zone',
                        'Non-respect du règlement',
                        'Demande personnelle',
                        'Problème de qualité récurrent'
                    ]);
                }
                return null;
            },
            'created_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Générer un numéro de téléphone marocain
     */
    private function generateMoroccanPhone(): string
    {
        $prefixes = ['06', '07', '05']; // Préfixes mobiles marocains courants
        $prefix = $this->faker->randomElement($prefixes);
        $number = $this->faker->numerify('########');
        return $prefix . $number;
    }

    /**
     * Supprimer les accents pour les emails
     */
    private function removeAccents(string $string): string
    {
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n'
        ];
        
        return strtr($string, $accents);
    }

    /**
     * État pour un membre actif
     */
    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'actif',
            'raison_suppression' => null,
        ]);
    }

    /**
     * État pour un membre inactif
     */
    public function inactif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'inactif',
            'raison_suppression' => null,
        ]);
    }

    /**
     * État pour un membre en suppression
     */
    public function enSuppression(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'suppression',
            'raison_suppression' => $this->faker->randomElement([
                'Arrêt d\'activité',
                'Déménagement hors zone',
                'Non-respect du règlement',
                'Demande personnelle',
                'Problème de qualité récurrent'
            ]),
        ]);
    }
}