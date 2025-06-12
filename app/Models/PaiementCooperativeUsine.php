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
        'prix_unitaire',
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
            'date_paiement' => 'date',
            'montant' => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'quantite_litres' => 'decimal:2',
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
     * Get formatted quantity.
     */
    public function getQuantiteFormatteeAttribute()
    {
        return number_format($this->quantite_litres, 2) . ' L';
    }

    /**
     * Get formatted unit price.
     */
    public function getPrixFormatteeAttribute()
    {
        return number_format($this->prix_unitaire, 2) . ' DH/L';
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
     * Get quinzaine label from date.
     */
    public function getQuinzaineLabelAttribute()
    {
        $day = $this->date_paiement->day;
        $month = $this->date_paiement->translatedFormat('F Y');
        
        if ($day <= 15) {
            return "1-15 {$month}";
        } else {
            $endDay = $this->date_paiement->endOfMonth()->day;
            return "16-{$endDay} {$month}";
        }
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
     * Create quinzaine payment.
     */
    public static function creerPaiementQuinzaine($cooperativeId, $dateDebut, $dateFin, $quantiteTotale, $prixUnitaire)
    {
        $montantTotal = $quantiteTotale * $prixUnitaire;
        
        return self::create([
            'id_cooperative' => $cooperativeId,
            'date_paiement' => $dateFin, // Date de fin de quinzaine
            'montant' => $montantTotal,
            'prix_unitaire' => $prixUnitaire,
            'quantite_litres' => $quantiteTotale,
            'statut' => 'en_attente'
        ]);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paiement) {
            // Recalculate montant if not set
            if (!$paiement->montant && $paiement->quantite_litres && $paiement->prix_unitaire) {
                $paiement->montant = $paiement->quantite_litres * $paiement->prix_unitaire;
            }
        });

        static::updating(function ($paiement) {
            // Recalculate montant if quantity or price changed
            if ($paiement->isDirty(['quantite_litres', 'prix_unitaire'])) {
                $paiement->montant = $paiement->quantite_litres * $paiement->prix_unitaire;
            }
        });
    }
}