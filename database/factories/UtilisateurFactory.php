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
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Utilisateur::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Arabic/French names for Morocco
        $arabicNames = [
            'أحمد محمد العلوي',
            'فاطمة الزهراء بنعلي',
            'محمد عبد الرحمن الإدريسي',
            'عائشة خديجة المرابط',
            'عبد الله يوسف التازي',
            'زينب نور الدين الفاسي',
            'إبراهيم حسن الأندلسي',
            'خديجة أمينة الشرقي',
            'عثمان سعيد الغربي',
            'مريم ليلى القادري',
            'حمزة طارق الريفي',
            'نادية سلمى الصحراوي',
            'يوسف كريم الأطلسي',
            'هند وردة الداودي',
            'سعد الدين محمود البركاني'
        ];

        return [
            'matricule' => $this->generateUniqueMatricule(),
            'nom_complet' => $this->faker->randomElement($arabicNames),
            'email' => $this->faker->unique()->safeEmail(),
            'mot_de_passe' => Hash::make('password123'), // Default password
            'telephone' => $this->generateMoroccanPhone(),
            'role' => $this->faker->randomElement(['éleveur', 'gestionnaire', 'usva', 'direction']),
            'statut' => $this->faker->randomElement(['actif', 'inactif']),
        ];
    }

    /**
     * Generate a unique matricule.
     */
    private function generateUniqueMatricule(): string
    {
        do {
            $matricule = str_pad($this->faker->numberBetween(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (Utilisateur::where('matricule', $matricule)->exists());

        return $matricule;
    }

    /**
     * Generate a Moroccan phone number format.
     */
    private function generateMoroccanPhone(): string
    {
        $prefixes = ['06', '07', '05']; // Common Moroccan mobile prefixes
        $prefix = $this->faker->randomElement($prefixes);
        $number = $this->faker->numerify('########');
        
        return $prefix . $number;
    }

    /**
     * Indicate that the user should be active.
     */
    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'actif',
        ]);
    }

    /**
     * Indicate that the user should be inactive.
     */
    public function inactif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => 'inactif',
        ]);
    }

    /**
     * Create an éleveur user.
     */
    public function eleveur(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'éleveur',
        ]);
    }

    /**
     * Create a gestionnaire user.
     */
    public function gestionnaire(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'gestionnaire',
        ]);
    }

    /**
     * Create a usva user.
     */
    public function usva(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'usva',
        ]);
    }

    /**
     * Create a direction user.
     */
    public function direction(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'direction',
        ]);
    }

    /**
     * Create a user with a specific password.
     */
    public function withPassword(string $password): static
    {
        return $this->state(fn (array $attributes) => [
            'mot_de_passe' => Hash::make($password),
        ]);
    }

    /**
     * Create a user with a specific matricule.
     */
    public function withMatricule(string $matricule): static
    {
        return $this->state(fn (array $attributes) => [
            'matricule' => $matricule,
        ]);
    }
}