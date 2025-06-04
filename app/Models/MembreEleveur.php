<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembreEleveur extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'membres_eleveurs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_membre';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_cooperative',
        'nom_complet',
        'adresse',
        'telephone',
        'email',
        'numero_carte_nationale',
        'statut',
        'raison_suppression',
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
     * Get the cooperative that owns the member.
     */
    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'id_cooperative', 'id_cooperative');
    }

    /**
     * Scope a query to only include active members.
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope a query to only include inactive members.
     */
    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    /**
     * Scope a query to only include deleted members.
     */
    public function scopeSupprime($query)
    {
        return $query->where('statut', 'suppression');
    }

    /**
     * Scope a query to filter by cooperative.
     */
    public function scopeByCooperative($query, $cooperativeId)
    {
        return $query->where('id_cooperative', $cooperativeId);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope a query to search by name.
     */
    public function scopeSearchByName($query, $search)
    {
        return $query->where('nom_complet', 'like', '%' . $search . '%');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Check if member is active.
     */
    public function isActif()
    {
        return $this->statut === 'actif';
    }

    /**
     * Check if member is inactive.
     */
    public function isInactif()
    {
        return $this->statut === 'inactif';
    }

    /**
     * Check if member is deleted.
     */
    public function isSupprime()
    {
        return $this->statut === 'suppression';
    }

    /**
     * Activate the member.
     */
    public function activer()
    {
        $this->statut = 'actif';
        $this->raison_suppression = null;
        return $this->save();
    }

    /**
     * Deactivate the member.
     */
    public function desactiver()
    {
        $this->statut = 'inactif';
        return $this->save();
    }

    /**
     * Delete the member with reason.
     */
    public function supprimer($raison = null)
    {
        $this->statut = 'suppression';
        $this->raison_suppression = $raison;
        return $this->save();
    }

    /**
     * Get formatted member info.
     */
    public function getFullInfoAttribute()
    {
        return $this->nom_complet . ' (' . $this->numero_carte_nationale . ')';
    }

    /**
     * Get member status in French.
     */
    public function getStatutLabelAttribute()
    {
        return match($this->statut) {
            'actif' => 'Actif',
            'inactif' => 'Inactif',
            'suppression' => 'Supprimé',
            default => 'Inconnu'
        };
    }

    /**
     * Get member status color for UI.
     */
    public function getStatutColorAttribute()
    {
        return match($this->statut) {
            'actif' => 'success',
            'inactif' => 'warning',
            'suppression' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($membre) {
            // Any logic before creating a member
        });

        static::updating(function ($membre) {
            // If status is being changed to 'suppression' and no reason provided
            if ($membre->isDirty('statut') && $membre->statut === 'suppression' && empty($membre->raison_suppression)) {
                $membre->raison_suppression = 'Suppression sans raison spécifiée';
            }
        });
    }
    // Ajouter ces relations dans le modèle MembreEleveur.php existant

    /**
     * Get all paiements for this membre.
     */
    public function paiements()
    {
        return $this->hasMany(PaiementCooperativeEleveur::class, 'id_membre', 'id_membre');
    }

    /**
     * Get calculated paiements.
     */
    public function paiementsCalcules()
    {
        return $this->hasMany(PaiementCooperativeEleveur::class, 'id_membre', 'id_membre')
                    ->where('statut', 'calcule');
    }

    /**
     * Get paid paiements.
     */
    public function paiementsPayes()
    {
        return $this->hasMany(PaiementCooperativeEleveur::class, 'id_membre', 'id_membre')
                    ->where('statut', 'paye');
    }

    /**
     * Get total amount paid to this membre.
     */
    public function getTotalPaiementsAttribute()
    {
        return $this->paiementsPayes()->sum('montant_total');
    }

    /**
     * Get pending payments amount.
     */
    public function getMontantEnAttenteAttribute()
    {
        return $this->paiementsCalcules()->sum('montant_total');
    }

    /**
     * Get last payment for this membre.
     */
    public function getDernierPaiementAttribute()
    {
        return $this->paiementsPayes()->latest('date_paiement')->first();
    }
}