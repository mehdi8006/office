<?php

namespace Database\Factories;

use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Utilisateur>
 */
class UtilisateurFactory extends Factory
{
    protected $model = Utilisateur::class;

    /**
     * Noms marocains réalistes
     */
    private static $prenomsHommes = [
        'Mohamed', 'Ahmed', 'Hassan', 'Omar', 'Youssef', 'Khalid', 'Abderrahim', 'Said', 'Rachid', 'Mustapha',
        'Abdellah', 'Abdellatif', 'Brahim', 'Noureddine', 'Driss', 'Aziz', 'Hamid', 'Karim', 'Amine', 'Fouad'
    ];

    private static $prenomsFemmes = [
        'Fatima', 'Aicha', 'Khadija', 'Hafsa', 'Zahra', 'Amina', 'Salma', 'Maryam', 'Samira', 'Nabila',
        'Zineb', 'Latifa', 'Malika', 'Rachida', 'Souad', 'Houria', 'Siham', 'Naima', 'Hasna', 'Karima'
    ];

    private static $noms = [
        'Benali', 'Alaoui', 'Idrissi', 'Bennani', 'Berrada', 'Tazi', 'Fassi', 'Kettani', 'Chraibi', 'Benjelloun',
        'El Amrani', 'Ouali', 'Lahlou', 'Zouine', 'Bouazza', 'Rhazali', 'Cherkaoui', 'Mernissi', 'Bousfiha', 'Lamrani',
        'El Mansouri', 'Qadiri', 'Hilali', 'Sabri', 'Tahiri', 'Balafrej', 'Sefrioui', 'Belkadi', 'Naciri', 'Alami'
    ];

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $roles = ['éleveur', 'gestionnaire', 'usva', 'direction'];
        $genre = $this->faker->randomElement(['homme', 'femme']);
        
        $prenom = $genre === 'homme' 
            ? $this->faker->randomElement(self::$prenomsHommes)
            : $this->faker->randomElement(self::$prenomsFemmes);
        
        $nom = $this->faker->randomElement(self::$noms);
        $nomComplet = $prenom . ' ' . $nom;

        return [
            'matricule' => $this->generateMatricule(),
            'nom_complet' => $nomComplet,
            'email' => $this->generateEmail($prenom, $nom),
            'mot_de_passe' => Hash::make('password123'), // Mot de passe par défaut
            'telephone' => $this->generateTelephone(),
            'role' => $this->faker->randomElement($roles),
            'statut' => $this->faker->randomElement(['actif', 'inactif']),
        ];
    }

    /**
     * Générer un matricule unique de 10 chiffres
     */
    private function generateMatricule(): string
    {
        do {
            $matricule = str_pad($this->faker->numberBetween(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (Utilisateur::where('matricule', $matricule)->exists());

        return $matricule;
    }

    /**
     * Générer un email basé sur le nom
     */
    private function generateEmail(string $prenom, string $nom): string
    {
        $domains = ['gmail.com', 'yahoo.fr', 'hotmail.com', 'outlook.com'];
        $cleanPrenom = strtolower($this->removeAccents($prenom));
        $cleanNom = strtolower($this->removeAccents($nom));
        
        $patterns = [
            $cleanPrenom . '.' . $cleanNom,
            $cleanPrenom . $cleanNom,
            substr($cleanPrenom, 0, 1) . '.' . $cleanNom,
            $cleanPrenom . '.' . substr($cleanNom, 0, 1),
        ];

        $baseEmail = $this->faker->randomElement($patterns);
        $domain = $this->faker->randomElement($domains);
        
        $email = $baseEmail . '@' . $domain;
        
        // Assurer l'unicité
        $counter = 1;
        while (Utilisateur::where('email', $email)->exists()) {
            $email = $baseEmail . $counter . '@' . $domain;
            $counter++;
        }

        return $email;
    }

    /**
     * Générer un numéro de téléphone marocain
     */
    private function generateTelephone(): string
    {
        $prefixes = ['06', '07', '05']; // Préfixes mobiles marocains
        $prefix = $this->faker->randomElement($prefixes);
        $number = $this->faker->numberBetween(10000000, 99999999);
        
        return $prefix . $number;
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
     * État pour un utilisateur éleveur
     */
    public function eleveur(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'éleveur',
            'statut' => 'actif',
        ]);
    }

    /**
     * État pour un utilisateur gestionnaire
     */
    public function gestionnaire(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'gestionnaire',
            'statut' => 'actif',
        ]);
    }

    /**
     * État pour un utilisateur USVA
     */
    public function usva(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'usva',
            'statut' => 'actif',
        ]);
    }

    /**
     * État pour un utilisateur direction
     */
    public function direction(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'direction',
            'statut' => 'actif',
        ]);
    }

    /**
     * État pour un utilisateur actif
     */
    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'actif',
        ]);
    }

    /**
     * État pour un utilisateur inactif
     */
    public function inactif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'inactif',
        ]);
    }
}