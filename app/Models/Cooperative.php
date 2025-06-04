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
     * Get all members of the cooperative.
     */
    public function membres()
    {
        return $this->hasMany(MembreEleveur::class, 'id_cooperative', 'id_cooperative');
    }

    /**
     * Get only active members of the cooperative.
     */
    public function membresActifs()
    {
        return $this->hasMany(MembreEleveur::class, 'id_cooperative', 'id_cooperative')
                    ->where('statut', 'actif');
    }

    /**
     * Get only inactive members of the cooperative.
     */
    public function membresInactifs()
    {
        return $this->hasMany(MembreEleveur::class, 'id_cooperative', 'id_cooperative')
                    ->where('statut', 'inactif');
    }

    /**
     * Get only deleted members of the cooperative.
     */
    public function membresSupprimes()
    {
        return $this->hasMany(MembreEleveur::class, 'id_cooperative', 'id_cooperative')
                    ->where('statut', 'suppression');
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
     * Scope to include cooperatives with their member counts.
     */
    public function scopeWithMemberCounts($query)
    {
        return $query->withCount([
            'membres',
            'membresActifs',
            'membresInactifs',
            'membresSupprimes'
        ]);
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
     * Get total number of members.
     */
    public function getTotalMembresAttribute()
    {
        return $this->membres()->count();
    }

    /**
     * Get number of active members.
     */
    public function getTotalMembresActifsAttribute()
    {
        return $this->membresActifs()->count();
    }

    /**
     * Get number of inactive members.
     */
    public function getTotalMembresInactifsAttribute()
    {
        return $this->membresInactifs()->count();
    }

    /**
     * Get number of deleted members.
     */
    public function getTotalMembresSuprimesAttribute()
    {
        return $this->membresSupprimes()->count();
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
    // Ajouter ces relations dans le modèle Cooperative.php existant

    /**
     * Get all livraisons usine for this cooperative.
     */
    public function livraisonsUsine()
    {
        return $this->hasMany(LivraisonUsine::class, 'id_cooperative', 'id_cooperative');
    }

    /**
     * Get all paiements from usine for this cooperative.
     */
    public function paiementsUsine()
    {
        return $this->hasMany(PaiementCooperativeUsine::class, 'id_cooperative', 'id_cooperative');
    }

    /**
     * Get all paiements to eleveurs from this cooperative.
     */
    public function paiementsEleveurs()
    {
        return $this->hasMany(PaiementCooperativeEleveur::class, 'id_cooperative', 'id_cooperative');
    }

    /**
     * Get planned livraisons.
     */
    public function livraisonsPlanifiees()
    {
        return $this->hasMany(LivraisonUsine::class, 'id_cooperative', 'id_cooperative')
                    ->where('statut', 'planifiee');
    }

    /**
     * Get validated livraisons.
     */
    public function livraisonsValidees()
    {
        return $this->hasMany(LivraisonUsine::class, 'id_cooperative', 'id_cooperative')
                    ->where('statut', 'validee');
    }

    /**
     * Get paid livraisons.
     */
    public function livraisonsPayees()
    {
        return $this->hasMany(LivraisonUsine::class, 'id_cooperative', 'id_cooperative')
                    ->where('statut', 'payee');
    }

    /**
     * Get pending payments from usine.
     */
    public function paiementsUsineEnAttente()
    {
        return $this->hasMany(PaiementCooperativeUsine::class, 'id_cooperative', 'id_cooperative')
                    ->where('statut', 'en_attente');
    }

    /**
     * Get calculated payments to eleveurs.
     */
    public function paiementsEleveursCalcules()
    {
        return $this->hasMany(PaiementCooperativeEleveur::class, 'id_cooperative', 'id_cooperative')
                    ->where('statut', 'calcule');
    }

    /**
     * Get total livraisons amount.
     */
    public function getTotalLivraisonsAttribute()
    {
        return $this->livraisonsUsine()->sum('montant_total');
    }

    /**
     * Get total payments received from usine.
     */
    public function getTotalPaiementsUsineAttribute()
    {
        return $this->paiementsUsine()->where('statut', 'paye')->sum('montant');
    }

    /**
     * Get total payments made to eleveurs.
     */
    public function getTotalPaiementsEleveursAttribute()
    {
        return $this->paiementsEleveurs()->where('statut', 'paye')->sum('montant_total');
    }
}