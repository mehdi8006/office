<?php

namespace Database\Factories;

use App\Models\Cooperative;
use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cooperative>
 */
class CooperativeFactory extends Factory
{
    protected $model = Cooperative::class;

    /**
     * Noms de coopératives agricoles marocaines réalistes
     */
    private static $nomsCooperatives = [
        'Coopérative Agricole Atlas',
        'Coopérative Laitière Rif',
        'Coopérative Agricole Souss',
        'Coopérative Laitière Tadla',
        'Coopérative Agricole Chaouia',
        'Coopérative Laitière Gharb',
        'Coopérative Agricole Doukkala',
        'Coopérative Laitière Saiss',
        'Coopérative Agricole Haouz',
        'Coopérative Laitière Zaër',
        'Coopérative Agricole Loukkos',
        'Coopérative Laitière Mamora',
        'Coopérative Agricole Abda',
        'Coopérative Laitière Chiadma',
        'Coopérative Agricole Rehamna',
        'Coopérative Laitière Anti-Atlas',
        'Coopérative Agricole Moyen Atlas',
        'Coopérative Laitière Haut Atlas',
        'Coopérative Agricole Tafilalet',
        'Coopérative Laitière Oriental'
    ];

    /**
     * Villes et régions marocaines
     */
    private static $villes = [
        'Casablanca', 'Rabat', 'Fès', 'Marrakech', 'Agadir', 'Tanger', 'Meknès', 'Oujda',
        'Kénitra', 'Tétouan', 'Salé', 'Temara', 'Safi', 'Mohammedia', 'Khouribga', 'Beni Mellal',
        'El Jadida', 'Nador', 'Settat', 'Berrechid', 'Khemisset', 'Inezgane', 'Larache', 'Guelmim'
    ];

    /**
     * Quartiers/Zones agricoles
     */
    private static $quartiers = [
        'Zone Industrielle', 'Hay Al Massira', 'Hay Al Mohammadi', 'Quartier Administratif',
        'Zone Agricole', 'Douar', 'Hay Essalam', 'Hay Al Wifaq', 'Zone Rurale',
        'Commune Rurale', 'Centre Ville', 'Nouvelle Ville', 'Médina', 'Hay Al Houda'
    ];

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'matricule' => $this->generateMatricule(),
            'nom_cooperative' => $this->faker->unique()->randomElement(self::$nomsCooperatives),
            'adresse' => $this->generateAdresse(),
            'telephone' => $this->generateTelephone(),
            'email' => $this->generateEmail(),
            'statut' => $this->faker->randomElement(['actif', 'inactif']),
            'responsable_id' => null, // Sera assigné par le seeder
        ];
    }

    /**
     * Générer un matricule unique pour coopérative
     */
    private function generateMatricule(): string
    {
        do {
            $matricule = 'COOP' . str_pad($this->faker->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Cooperative::where('matricule', $matricule)->exists());

        return $matricule;
    }

    /**
     * Générer une adresse marocaine
     */
    private function generateAdresse(): string
    {
        $ville = $this->faker->randomElement(self::$villes);
        $quartier = $this->faker->randomElement(self::$quartiers);
        $numero = $this->faker->numberBetween(1, 999);
        $rue = $this->faker->randomElement([
            'Avenue Mohammed V',
            'Rue Hassan II',
            'Boulevard Al Massira',
            'Avenue des FAR',
            'Rue de la Liberté',
            'Avenue Al Andalous',
            'Rue de l\'Independence',
            'Boulevard Zerktouni',
            'Avenue Prince Héritier',
            'Rue Ibn Battuta'
        ]);

        return "{$numero} {$rue}, {$quartier}, {$ville}";
    }

    /**
     * Générer un numéro de téléphone fixe marocain
     */
    private function generateTelephone(): string
    {
        $prefixes = ['05', '023', '024', '025', '026', '028', '029'];
        $prefix = $this->faker->randomElement($prefixes);
        
        if ($prefix === '05') {
            // Mobile
            $number = $this->faker->numberBetween(10000000, 99999999);
            return $prefix . $number;
        } else {
            // Fixe
            $number = $this->faker->numberBetween(100000, 999999);
            return $prefix . $number;
        }
    }

    /**
     * Générer un email pour coopérative
     */
    private function generateEmail(): string
    {
        $domains = ['gmail.com', 'hotmail.com', 'yahoo.fr', 'outlook.com'];
        $domain = $this->faker->randomElement($domains);
        
        $baseNames = [
            'cooperative.lait', 'coop.agricole', 'laiterie', 'elevage',
            'ferme.lait', 'agri.coop', 'lait.frais', 'cooperative.rurale'
        ];
        
        $baseName = $this->faker->randomElement($baseNames);
        $number = $this->faker->numberBetween(1, 999);
        
        $email = $baseName . $number . '@' . $domain;
        
        // Assurer l'unicité
        $counter = 1;
        while (Cooperative::where('email', $email)->exists()) {
            $email = $baseName . $number . $counter . '@' . $domain;
            $counter++;
        }

        return $email;
    }

    /**
     * État pour une coopérative active
     */
    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'actif',
        ]);
    }

    /**
     * État pour une coopérative inactive
     */
    public function inactif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'inactif',
        ]);
    }

    /**
     * État avec responsable assigné
     */
    public function withResponsable(): static
    {
        return $this->state(function (array $attributes) {
            // Créer un gestionnaire ou utiliser un existant
            $gestionnaire = Utilisateur::where('role', 'gestionnaire')
                                     ->where('statut', 'actif')
                                     ->whereDoesntHave('cooperatives')
                                     ->first();
            
            if (!$gestionnaire) {
                $gestionnaire = Utilisateur::factory()->gestionnaire()->create();
            }

            return [
                'responsable_id' => $gestionnaire->id_utilisateur,
                'statut' => 'actif',
            ];
        });
    }

    /**
     * État pour coopérative dans une région spécifique
     */
    public function inRegion(string $region): static
    {
        $adressesParRegion = [
            'Casablanca-Settat' => ['Casablanca', 'Settat', 'Berrechid', 'Mohammedia'],
            'Rabat-Salé-Kénitra' => ['Rabat', 'Salé', 'Kénitra', 'Temara'],
            'Fès-Meknès' => ['Fès', 'Meknès', 'Khemisset'],
            'Marrakech-Safi' => ['Marrakech', 'Safi', 'El Jadida'],
            'Souss-Massa' => ['Agadir', 'Inezgane'],
            'Tanger-Tétouan-Al Hoceïma' => ['Tanger', 'Tétouan', 'Larache'],
        ];

        return $this->state(function (array $attributes) use ($region, $adressesParRegion) {
            $villes = $adressesParRegion[$region] ?? self::$villes;
            $ville = $this->faker->randomElement($villes);
            $quartier = $this->faker->randomElement(self::$quartiers);
            $numero = $this->faker->numberBetween(1, 999);
            $rue = $this->faker->randomElement([
                'Avenue Mohammed V', 'Rue Hassan II', 'Boulevard Al Massira'
            ]);

            return [
                'adresse' => "{$numero} {$rue}, {$quartier}, {$ville}",
            ];
        });
    }
}