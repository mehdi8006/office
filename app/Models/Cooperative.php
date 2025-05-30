<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cooperative extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cooperatives';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_cooperative';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'matricule',
        'nom_cooperative',
        'adresse',
        'telephone',
        'email',
        'statut',
        'responsable_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the responsable (user) of the cooperative.
     */
    public function responsable()
    {
        return $this->belongsTo(Utilisateur::class, 'responsable_id', 'id_utilisateur');
    }

    /**
     * Scope a query to only include active cooperatives.
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope a query to only include inactive cooperatives.
     */
    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    /**
     * Scope a query to filter by matricule.
     */
    public function scopeByMatricule($query, $matricule)
    {
        return $query->where('matricule', $matricule);
    }

    /**
     * Check if cooperative is active.
     */
    public function isActif()
    {
        return $this->statut === 'actif';
    }

    /**
     * Check if cooperative is inactive.
     */
    public function isInactif()
    {
        return $this->statut === 'inactif';
    }

    /**
     * Generate a unique matricule for cooperative.
     */
    public static function generateMatricule()
    {
        do {
            // Generate matricule with format: COOP + 6 digits
            $matricule = 'COOP' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('matricule', $matricule)->exists());

        return $matricule;
    }

    /**
     * Get the full name with matricule.
     */
    public function getFullNameAttribute()
    {
        return $this->matricule . ' - ' . $this->nom_cooperative;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cooperative) {
            if (empty($cooperative->matricule)) {
                $cooperative->matricule = self::generateMatricule();
            }
        });
    }
}