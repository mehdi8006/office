<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'utilisateurs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_utilisateur';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'matricule',
        'nom_complet',
        'email',
        'mot_de_passe',
        'telephone',
        'role',
        'statut',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mot_de_passe' => 'hashed',
        ];
    }

    /**
     * Get the password attribute name for authentication.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id_utilisateur';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope a query to filter by role.
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to filter by matricule.
     */
    public function scopeByMatricule($query, $matricule)
    {
        return $query->where('matricule', $matricule);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is active.
     */
    public function isActif()
    {
        return $this->statut === 'actif';
    }

    /**
     * Generate a unique matricule.
     */
    public static function generateMatricule()
    {
        do {
            $matricule = str_pad(random_int(1, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (self::where('matricule', $matricule)->exists());

        return $matricule;
    }
    

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($utilisateur) {
            if (empty($utilisateur->matricule)) {
                $utilisateur->matricule = self::generateMatricule();
            }
        });
    }
    public function cooperatives()
    {
        return $this->hasMany(Cooperative::class, 'responsable_id', 'id_utilisateur');
    }
// Ajouter ces méthodes à la fin de la classe Utilisateur dans app/Models/Utilisateur.php

/**
 * Get the cooperative managed by this gestionnaire.
 */
public function cooperativeGeree()
{
    return $this->hasOne(Cooperative::class, 'responsable_id', 'id_utilisateur');
}

/**
 * Check if user is a gestionnaire with assigned cooperative.
 */
public function isGestionnaireWithCooperative()
{
    return $this->role === 'gestionnaire' && $this->cooperativeGeree !== null;
}

/**
 * Get the cooperative ID for this gestionnaire.
 */
public function getCooperativeId()
{
    if ($this->role !== 'gestionnaire') {
        return null;
    }
    
    $cooperative = $this->cooperativeGeree;
    return $cooperative ? $cooperative->id_cooperative : null;
}

/**
 * Check if gestionnaire can access a specific membre.
 */
public function canAccessMembre($membreOrCooperativeId)
{
    if ($this->role !== 'gestionnaire') {
        return false;
    }
    
    $cooperativeId = $this->getCooperativeId();
    if (!$cooperativeId) {
        return false;
    }
    
    // Si c'est un ID de coopérative
    if (is_numeric($membreOrCooperativeId)) {
        return $cooperativeId == $membreOrCooperativeId;
    }
    
    // Si c'est un objet membre
    if (is_object($membreOrCooperativeId) && isset($membreOrCooperativeId->id_cooperative)) {
        return $cooperativeId == $membreOrCooperativeId->id_cooperative;
    }
    
    return false;
}
}