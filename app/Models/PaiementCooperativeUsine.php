<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaiementCooperativeUsine extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'paiements_cooperative_usine';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_paiement';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_cooperative',
        'date_paiement',
        'montant',
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
            'date_paiement' => 'date',
            'montant' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the cooperative that owns the paiement.
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
        return $query->whereDate('date_paiement', $date);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_paiement', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope a query to get pending payments.
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope a query to get paid payments.
     */
    public function scopePaye($query)
    {
        return $query->where('statut', 'paye');
    }

    /**
     * Scope to order by date (most recent first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('date_paiement', 'desc');
    }

    /**
     * Check if payment is pending.
     */
    public function isEnAttente()
    {
        return $this->statut === 'en_attente';
    }

    /**
     * Check if payment is paid.
     */
    public function isPaye()
    {
        return $this->statut === 'paye';
    }

    /**
     * Mark payment as paid.
     */
    public function marquerPaye()
    {
        $this->statut = 'paye';
        $this->date_paiement = now()->toDateString();
        
        return $this->save();
    }

    /**
     * Get formatted amount.
     */
    public function getMontantFormatteeAttribute()
    {
        return number_format($this->montant, 2) . ' DH';
    }

    /**
     * Get status label in French.
     */
    public function getStatutLabelAttribute()
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'paye' => 'Payé',
            default => 'Inconnu'
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatutColorAttribute()
    {
        return match($this->statut) {
            'en_attente' => 'warning',
            'paye' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get total amount by cooperative.
     */
    public static function getTotalByCooperative($cooperativeId, $startDate = null, $endDate = null)
    {
        $query = self::where('id_cooperative', $cooperativeId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('date_paiement', [$startDate, $endDate]);
        }
        
        return $query->sum('montant');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Removed the creating callback that set payment amount from livraison
        // since we no longer have the livraison relationship
    }
}