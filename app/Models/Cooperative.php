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
        'nom_cooperative',
        'adresse',
        'telephone',
        'email',
        'statut',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
     * Scope a query to filter by status.
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope a query to search cooperatives by name.
     */
    public function scopeSearchByName($query, $search)
    {
        return $query->where('nom_cooperative', 'like', '%' . $search . '%');
    }

    /**
     * Scope a query to search cooperatives by email.
     */
    public function scopeSearchByEmail($query, $search)
    {
        return $query->where('email', 'like', '%' . $search . '%');
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
     * Activate the cooperative.
     */
    public function activate()
    {
        $this->update(['statut' => 'actif']);
    }

    /**
     * Deactivate the cooperative.
     */
    public function deactivate()
    {
        $this->update(['statut' => 'inactif']);
    }

    /**
     * Get the cooperative's status in a human-readable format.
     */
    public function getStatutLabelAttribute()
    {
        return $this->statut === 'actif' ? 'Actif' : 'Inactif';
    }

    /**
     * Get the cooperative's formatted creation date.
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Get the cooperative's formatted update date.
     */
    public function getFormattedUpdatedAtAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }
}