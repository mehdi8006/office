<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivraisonUsine extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'livraisons_usine';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_livraison';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_cooperative',
        'date_livraison',
        'quantite_litres',
        'statut',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_livraison' => 'date',
            'quantite_litres' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the cooperative that owns the livraison.
     */
    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'id_cooperative', 'id_cooperative');
    }

    /**
     * Scope a query to filter by cooperative.
     */
    public function scopeByCooperative($query, $cooperativeId)
    {
        return $query->where('id_cooperative', $cooperativeId);
    }

    /**
     * Scope a query to filter by date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date_livraison', $date);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_livraison', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope a query to get planned deliveries.
     */
    public function scopePlanifiee($query)
    {
        return $query->where('statut', 'planifiee');
    }

    /**
     * Scope a query to get validated deliveries.
     */
    public function scopeValidee($query)
    {
        return $query->where('statut', 'validee');
    }

    /**
     * Scope to order by date (most recent first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('date_livraison', 'desc');
    }

    /**
     * Check if livraison is planned.
     */
    public function isPlanifiee()
    {
        return $this->statut === 'planifiee';
    }

    /**
     * Check if livraison is validated.
     */
    public function isValidee()
    {
        return $this->statut === 'validee';
    }

    /**
     * Validate the livraison.
     */
    public function valider()
    {
        $this->statut = 'validee';
        return $this->save();
    }

    /**
     * Get formatted quantity.
     */
    public function getQuantiteFormatteeAttribute()
    {
        return number_format($this->quantite_litres, 2) . ' L';
    }

    /**
     * Get status label in French.
     */
    public function getStatutLabelAttribute()
    {
        return match($this->statut) {
            'planifiee' => 'Planifiée',
            'validee' => 'Validée',
            default => 'Inconnu'
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatutColorAttribute()
    {
        return match($this->statut) {
            'planifiee' => 'warning',
            'validee' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Plus besoin de calculer montant_total car c'est maintenant un accessor
        // qui calcule en temps réel : quantite_litres * PRIX_UNITAIRE_FIXE
    }
}