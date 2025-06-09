<?php

namespace Database\Factories;

use App\Models\MembreEleveur;
use App\Models\Cooperative;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembreEleveur>
 */
class MembreEleveurFactory extends Factory
{
    protected $model = MembreEleveur::class;

    /**
     * Noms marocains pour éleveurs
     */
    private static $prenomsHommes = [
        'Mohamed', 'Ahmed', 'Hassan', 'Omar', 'Youssef', 'Khalid', 'Abderrahim', 'Said', 'Rachid', 'Mustapha',
        'Abdellah', 'Abdellatif', 'Brahim', 'Noureddine', 'Driss', 'Aziz', 'Hamid', 'Karim', 'Amine', 'Fouad',
        'Lahcen', 'Moha', 'Abdelmajid', 'Abdelouahab', 'Abdessamad', 'Allal', 'Haj', 'Larbi', 'Tahar', 'Mbarek'
    ];

    private static $prenomsFemmes = [
        'Fatima', 'Aicha', 'Khadija', 'Hafsa', 'Zahra', 'Amina', 'Salma', 'Maryam', 'Samira', 'Nabila',
        'Zineb', 'Latifa', 'Malika', 'Rachida', 'Souad', 'Houria', 'Siham', 'Naima', 'Hasna', 'Karima',
        'Lalla', 'Hajja', 'Zoubida', 'Mbarka', 'Rahma', 'Saida', 'Fadma', 'Yamna', 'Touria', 'Khadija'
    ];

    private static $noms = [
        'Benali', 'Alaoui', 'Idrissi', 'Bennani', 'Berrada', 'Tazi', 'Fassi', 'Kettani', 'Chraibi', 'Benjelloun',
        'El Amrani', 'Ouali', 'Lahlou', 'Zouine', 'Bouazza', 'Rhazali', 'Cherkaoui', 'Mernissi', 'Bousfiha', 'Lamrani',
        'El Mansouri', 'Qadiri', 'Hilali', 'Sabri', 'Tahiri', 'Balafrej', 'Sefrioui', 'Belkadi', 'Naciri', 'Alami',
        'Ait Ali', 'Ait Brahim', 'Ait Youssef', 'Ait Omar', 'El Fassi', 'El Alami', 'Ben Moussa', 'Ben Aissa',
        'Hajji', 'Guerraoui', 'Andaloussi', 'Squalli', 'Tounsi', 'Slaoui', 'Marrakchi', 'Casablanci'
    ];

    /**
     * Douars et villages ruraux marocains
     */
    private static $douars = [
        'Douar Ait Melloul', 'Douar Oulad Ahmed', 'Douar Ait Youssef', 'Douar Ben Slimane',
        'Douar Oulad Ali', 'Douar Ait Brahim', 'Douar Sidi Mohamed', 'Douar Ait Hassan',
        'Douar Oulad Tayeb', 'Douar Ait Omar', 'Douar Sidi Abdellah', 'Douar Ait Hammou',
        'Douar Oulad Driss', 'Douar Ait Lahcen', 'Douar Sidi Brahim', 'Douar Ait Mhand',
        'Douar Oulad Moha', 'Douar Ait Bella', 'Douar Sidi Youssef', 'Douar Ait Daoud'
    ];

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $genre = $this->faker->randomElement(['homme', 'femme']);
        
        $prenom = $genre === 'homme' 
            ? $this->faker->randomElement(self::$prenomsHommes)
            : $this->faker->randomElement(self::$prenomsFemmes);
        
        $nom = $this->faker->randomElement(self::$noms);
        $nomComplet = $prenom . ' ' . $nom;

        return [
            'id_cooperative' => Cooperative::factory(),
            'nom_complet' => $nomComplet,
            'adresse' => $this->generateAdresseRurale(),
            'telephone' => $this->generateTelephone(),
            'email' => $this->generateEmail($prenom, $nom),
            'numero_carte_nationale' => $this->generateCarteNationale(),
            'statut' => $this->faker->randomElement(['actif', 'inactif', 'suppression']),
            'raison_suppression' => null,
        ];
    }

    /**
     * Générer une adresse rurale
     */
    private function generateAdresseRurale(): string
    {
        $douar = $this->faker->randomElement(self::$douars);
        $commune = $this->faker->randomElement([
            'Commune Rurale Ait Melloul',
            'Commune Rurale Sidi Brahim',
            'Commune Rurale Oulad Ahmed',
            'Commune Rurale Ben Slimane',
            'Commune Rurale Ait Youssef',
            'Commune Rurale Sidi Mohamed',
            'Commune Rurale Oulad Ali',
            'Commune Rurale Ait Hassan'
        ]);

        $province = $this->faker->randomElement([
            'Province de Khouribga', 'Province de Settat', 'Province de Berrechid',
            'Province de Benslimane', 'Province de Sidi Bennour', 'Province de El Jadida',
            'Province de Safi', 'Province de Youssoufia', 'Province de Kénitra',
            'Province de Sidi Kacem', 'Province de Khemisset', 'Province de Sale'
        ]);

        return "{$douar}, {$commune}, {$province}";
    }

    /**
     * Générer un numéro de téléphone mobile marocain
     */
    private function generateTelephone(): string
    {
        $prefixes = ['06', '07']; // Mobiles principalement en milieu rural
        $prefix = $this->faker->randomElement($prefixes);
        $number = $this->faker->numberBetween(10000000, 99999999);
        
        return $prefix . $number;
    }

    /**
     * Générer un email
     */
    private function generateEmail(string $prenom, string $nom): string
    {
        $domains = ['gmail.com', 'yahoo.fr', 'hotmail.com'];
        $cleanPrenom = strtolower($this->removeAccents($prenom));
        $cleanNom = strtolower($this->removeAccents($nom));
        
        $patterns = [
            $cleanPrenom . '.' . $cleanNom,
            $cleanPrenom . $cleanNom,
            substr($cleanPrenom, 0, 1) . '.' . $cleanNom,
            $cleanPrenom . '.' . substr($cleanNom, 0, 1),
            $cleanPrenom . $this->faker->numberBetween(1, 999),
        ];

        $baseEmail = $this->faker->randomElement($patterns);
        $domain = $this->faker->randomElement($domains);
        
        $email = $baseEmail . '@' . $domain;
        
        // Assurer l'unicité
        $counter = 1;
        while (MembreEleveur::where('email', $email)->exists()) {
            $email = $baseEmail . $counter . '@' . $domain;
            $counter++;
        }

        return $email;
    }

    /**
     * Générer un numéro de carte nationale marocaine
     */
    private function generateCarteNationale(): string
    {
        do {
            // Format: 2 lettres + 6 chiffres (simulé)
            $lettres = strtoupper($this->faker->randomLetter . $this->faker->randomLetter);
            $chiffres = str_pad($this->faker->numberBetween(100000, 999999), 6, '0', STR_PAD_LEFT);
            $carte = $lettres . $chiffres;
        } while (MembreEleveur::where('numero_carte_nationale', $carte)->exists());

        return $carte;
    }

    /**
     * Enlever les accents
     */
    private function removeAccents(string $string): string
    {
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i',
            'î' => 'i', 'ï' => 'i', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o',
            'õ' => 'o', 'ö' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y'
        ];
        
        return strtr($string, $accents);
    }

    /**
     * État pour membre actif
     */
    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'actif',
            'raison_suppression' => null,
        ]);
    }

    /**
     * État pour membre inactif
     */
    public function inactif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'inactif',
            'raison_suppression' => null,
        ]);
    }

    /**
     * État pour membre supprimé
     */
    public function supprime(): static
    {
        $raisons = [
            'Déménagement hors zone',
            'Arrêt de l\'activité d\'élevage',
            'Non-respect du règlement',
            'Demande du membre',
            'Inactivité prolongée',
            'Fusion avec autre exploitation'
        ];

        return $this->state(fn (array $attributes) => [
            'statut' => 'suppression',
            'raison_suppression' => $this->faker->randomElement($raisons),
        ]);
    }

    /**
     * État pour membre d'une coopérative spécifique
     */
    public function forCooperative(Cooperative $cooperative): static
    {
        return $this->state(fn (array $attributes) => [
            'id_cooperative' => $cooperative->id_cooperative,
        ]);
    }

    /**
     * État pour membre récemment ajouté
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'statut' => 'actif',
                'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    /**
     * État pour ancien membre
     */
    public function ancien(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
            ];
        });
    }
}