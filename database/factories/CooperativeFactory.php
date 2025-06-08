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
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Noms de coopératives laitières réalistes au Maroc
        $nomsCooperatives = [
            'Coopérative Laitière Al Baraka',
            'Coopérative Agro-Pastorale Al Majd',
            'Coopérative des Éleveurs de l\'Atlas',
            'Coopérative Laitière Oum Rabia',
            'Coopérative Al Hoceima Lait',
            'Coopérative Agricole Souss Massa',
            'Coopérative Laitière Tadla Azilal',
            'Coopérative des Bergers du Rif',
            'Coopérative Al Wahda Laitière',
            'Coopérative Agro-Pastorale Doukkala',
            'Coopérative Laitière Chaouia',
            'Coopérative Al Firdaws Lait',
            'Coopérative des Éleveurs Zaërs',
            'Coopérative Laitière Gharb',
            'Coopérative Al Nour Agro-Pastorale',
            'Coopérative Laitière Oriental',
            'Coopérative Agro-Pastorale Haouz',
            'Coopérative Al Karama Lait',
            'Coopérative des Producteurs Khénifra',
            'Coopérative Laitière Béni Mellal',
            'Coopérative Al Amal des Éleveurs',
            'Coopérative Agro-Pastorale Tafilalet',
            'Coopérative Laitière Anti-Atlas',
            'Coopérative Al Mountada Lait',
            'Coopérative des Bergers Fès-Meknès',
            'Coopérative Laitière Moyen Atlas',
            'Coopérative Al Ikhlass Agro-Pastorale',
            'Coopérative des Éleveurs Rabat-Salé',
            'Coopérative Laitière Casablanca-Settat',
            'Coopérative Al Wifaq Lait'
        ];

        // Régions et villes du Maroc pour les adresses
        $regions = [
            'Casablanca-Settat' => ['Casablanca', 'Settat', 'Berrechid', 'Mohammedia', 'El Jadida'],
            'Rabat-Salé-Kénitra' => ['Rabat', 'Salé', 'Kénitra', 'Témara', 'Khemisset'],
            'Fès-Meknès' => ['Fès', 'Meknès', 'Taza', 'Sefrou', 'Ifrane'],
            'Marrakech-Safi' => ['Marrakech', 'Safi', 'Essaouira', 'Kelâa des Sraghna'],
            'Souss-Massa' => ['Agadir', 'Taroudant', 'Tiznit', 'Ouarzazate'],
            'Tanger-Tétouan-Al Hoceïma' => ['Tanger', 'Tétouan', 'Al Hoceïma', 'Larache'],
            'Oriental' => ['Oujda', 'Nador', 'Berkane', 'Taourirt'],
            'Drâa-Tafilalet' => ['Errachidia', 'Ouarzazate', 'Zagora', 'Midelt'],
            'Béni Mellal-Khénifra' => ['Béni Mellal', 'Khénifra', 'Khouribga', 'Azilal'],
            'Guelmim-Oued Noun' => ['Guelmim', 'Tan-Tan', 'Sidi Ifni']
        ];

        $regionSelectionnee = $this->faker->randomElement(array_keys($regions));
        $ville = $this->faker->randomElement($regions[$regionSelectionnee]);

        // Générer le nom de la coopérative
        $nomCooperative = $this->faker->unique()->randomElement($nomsCooperatives);

        // Adresse réaliste
        $adresse = $this->genererAdresseRealistic($ville, $regionSelectionnee);

        // Email professionnel basé sur le nom
        $emailBase = $this->genererEmailProfessionnel($nomCooperative);

        return [
            'matricule' => $this->genererMatriculeUnique(),
            'nom_cooperative' => $nomCooperative,
            'adresse' => $adresse,
            'telephone' => $this->genererTelephoneProfessionnel(),
            'email' => $emailBase,
            'statut' => $this->faker->randomElement([
                'actif' => 90,      // 90% actives
                'inactif' => 10     // 10% inactives
            ]),
            'responsable_id' => null, // Sera assigné dans le seeder si des utilisateurs existent
            'created_at' => $this->faker->dateTimeBetween('-3 years', '-6 months'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Générer un matricule unique pour la coopérative
     */
    private function genererMatriculeUnique(): string
    {
        // Format: COOP + Année + Numéro séquentiel (ex: COOP240001)
        $annee = $this->faker->numberBetween(21, 24); // 2021-2024
        $sequence = str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "COOP{$annee}{$sequence}";
    }

    /**
     * Générer une adresse réaliste au Maroc
     */
    private function genererAdresseRealistic(string $ville, string $region): string
    {
        $typesAdresses = [
            'zones_rurales' => [
                'Douar {nom}',
                'Commune Rurale de {nom}',
                'Village {nom}', 
                'Fraction {nom}',
                'Bled {nom}'
            ],
            'zones_urbaines' => [
                'Quartier {nom}',
                'Secteur {nom}',
                'Zone Industrielle {nom}',
                'Avenue {nom}',
                'Boulevard {nom}'
            ]
        ];

        $nomsLieux = [
            'Al Baraka', 'Salam', 'Al Houda', 'Nour', 'Al Wifaq', 'Annahda', 'Al Karama',
            'Oum Rabia', 'Al Majd', 'Al Firdaws', 'Azzaitoune', 'Al Mountada', 'Al Amal',
            'Hassan II', 'Mohammed V', 'Al Massira', 'Al Qods', 'Al Andalous', 'Al Wahda'
        ];

        // 70% des coopératives sont en zone rurale, 30% en zone urbaine
        $typeZone = $this->faker->randomElement(['zones_rurales' => 70, 'zones_urbaines' => 30]);
        
        $modeleAdresse = $this->faker->randomElement($typesAdresses[$typeZone]);
        $nomLieu = $this->faker->randomElement($nomsLieux);
        $adresseBase = str_replace('{nom}', $nomLieu, $modeleAdresse);

        // Ajouter un numéro si c'est une adresse urbaine
        if ($typeZone === 'zones_urbaines') {
            $numero = $this->faker->numberBetween(1, 999);
            $adresseBase = "{$numero}, {$adresseBase}";
        }

        return "{$adresseBase}, {$ville}, {$region}";
    }

    /**
     * Générer un email professionnel
     */
    private function genererEmailProfessionnel(string $nomCooperative): string
    {
        // Extraire des mots clés du nom
        $mots = explode(' ', strtolower($nomCooperative));
        $motsCles = [];
        
        foreach ($mots as $mot) {
            if (strlen($mot) > 3 && !in_array($mot, ['coopérative', 'des', 'laitière', 'agro', 'pastorale'])) {
                $motsCles[] = $this->removeAccents($mot);
            }
        }

        // Créer l'email
        if (count($motsCles) >= 2) {
            $emailBase = $motsCles[0] . $motsCles[1];
        } elseif (count($motsCles) === 1) {
            $emailBase = $motsCles[0] . 'coop';
        } else {
            $emailBase = 'cooperative' . rand(100, 999);
        }

        $domaines = ['@coop-maroc.ma', '@gmail.com', '@yahoo.fr', '@cooperative.ma', '@hotmail.com'];
        
        return $emailBase . $this->faker->randomElement($domaines);
    }

    /**
     * Générer un téléphone professionnel marocain
     */
    private function genererTelephoneProfessionnel(): string
    {
        // Préfixes fixes et mobiles marocains
        $prefixes = [
            '0528', '0529', '0524', '0525', '0526', '0527', // Fixes
            '0661', '0662', '0663', '0664', '0665', '0666', // Mobiles professionnels
            '0671', '0672', '0673', '0674', '0675', '0676'  // Autres mobiles
        ];
        
        $prefix = $this->faker->randomElement($prefixes);
        $numero = $this->faker->numerify('######');
        
        return $prefix . $numero;
    }

    /**
     * Supprimer les accents
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
     * État pour une coopérative active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'actif',
        ]);
    }

    /**
     * État pour une coopérative inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'inactif',
        ]);
    }

    /**
     * État pour une grande coopérative (avec responsable défini)
     */
    public function avecResponsable(): static
    {
        return $this->state(fn (array $attributes) => [
            'responsable_id' => Utilisateur::factory(),
        ]);
    }

    /**
     * État pour une coopérative récente
     */
    public function recente(): static
    {
        return $this->state(function (array $attributes) {
            $dateCreation = $this->faker->dateTimeBetween('-6 months', 'now');
            return [
                'created_at' => $dateCreation,
                'updated_at' => $this->faker->dateTimeBetween($dateCreation, 'now'),
            ];
        });
    }

    /**
     * État pour une coopérative dans une région spécifique
     */
    public function dansRegion(string $region): static
    {
        return $this->state(function (array $attributes) use ($region) {
            $regions = [
                'Casablanca-Settat' => ['Casablanca', 'Settat', 'Berrechid'],
                'Rabat-Salé-Kénitra' => ['Rabat', 'Kénitra', 'Salé'],
                'Fès-Meknès' => ['Fès', 'Meknès', 'Taza'],
                // ... autres régions
            ];

            if (isset($regions[$region])) {
                $ville = $this->faker->randomElement($regions[$region]);
                $adresse = $this->genererAdresseRealistic($ville, $region);
                
                return [
                    'adresse' => $adresse,
                ];
            }

            return [];
        });
    }

    /**
     * Configurer avec un responsable spécifique
     */
    public function avecResponsableSpecifique(int $idResponsable): static
    {
        return $this->state(fn (array $attributes) => [
            'responsable_id' => $idResponsable,
        ]);
    }

    /**
     * État pour une coopérative avec email personnalisé
     */
    public function avecEmailPersonnalise(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }
}