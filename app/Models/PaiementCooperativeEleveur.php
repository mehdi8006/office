<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PaiementCooperativeEleveur extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'paiements_cooperative_eleveurs';

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
        'id_membre',
        'id_cooperative',
        'periode_debut',
        'periode_fin',
        'quantite_totale',
        'prix_unitaire',
        'montant_total',
        'date_paiement',
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
            'periode_debut' => 'date',
            'periode_fin' => 'date',
            'date_paiement' => 'date',
            'quantite_totale' => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'montant_total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the membre (eleveur) that owns the paiement.
     */
    public function membre()
    {
        return $this->belongsTo(MembreEleveur::class, 'id_membre', 'id_membre');
    }

    /**
     * Get the cooperative that owns the paiement.
     */
    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'id_cooperative', 'id_cooperative');
    }

    /**
     * Scope a query to filter by membre.
     */
    public function scopeByMembre($query, $membreId)
    {
        return $query->where('id_membre', $membreId);
    }

    /**
     * Scope a query to filter by cooperative.
     */
    public function scopeByCooperative($query, $cooperativeId)
    {
        return $query->where('id_cooperative', $cooperativeId);
    }

    /**
     * Scope a query to filter by period.
     */
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->where('periode_debut', '>=', $startDate)
                    ->where('periode_fin', '<=', $endDate);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope a query to get calculated payments.
     */
    public function scopeCalcule($query)
    {
        return $query->where('statut', 'calcule');
    }

    /**
     * Scope a query to get paid payments.
     */
    public function scopePaye($query)
    {
        return $query->where('statut', 'paye');
    }

    /**
     * Scope to order by period (most recent first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('periode_fin', 'desc')
                    ->orderBy('periode_debut', 'desc');
    }

    /**
     * Check if payment is calculated.
     */
    public function isCalcule()
    {
        return $this->statut === 'calcule';
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
     * Get formatted quantity.
     */
    public function getQuantiteFormatteeAttribute()
    {
        return number_format($this->quantite_totale, 2) . ' L';
    }

    /**
     * Get formatted price.
     */
    public function getPrixFormatteeAttribute()
    {
        return number_format($this->prix_unitaire, 2) . ' DH/L';
    }

    /**
     * Get formatted total amount.
     */
    public function getMontantFormatteeAttribute()
    {
        return number_format($this->montant_total, 2) . ' DH';
    }

    /**
     * Get period label.
     */
    public function getPeriodeLabelAttribute()
    {
        return $this->periode_debut->format('d/m/Y') . ' - ' . $this->periode_fin->format('d/m/Y');
    }

    /**
     * Get status label in French.
     */
    public function getStatutLabelAttribute()
    {
        return match($this->statut) {
            'calcule' => 'Calculé',
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
            'calcule' => 'warning',
            'paye' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Calculate payment for a member for a specific period.
     */
    public static function calculerPaiement($membreId, $cooperativeId, $startDate, $endDate, $prixUnitaire)
    {
        // Get total quantity from receptions for this period
        $quantiteTotale = ReceptionLait::where('id_membre', $membreId)
                                     ->where('id_cooperative', $cooperativeId)
                                     ->whereBetween('date_reception', [$startDate, $endDate])
                                     ->sum('quantite_litres');

        // Calculate montant
        $montantTotal = $quantiteTotale * $prixUnitaire;

        // Create or update payment
        $paiement = self::updateOrCreate([
            'id_membre' => $membreId,
            'id_cooperative' => $cooperativeId,
            'periode_debut' => $startDate,
            'periode_fin' => $endDate,
        ], [
            'quantite_totale' => $quantiteTotale,
            'prix_unitaire' => $prixUnitaire,
            'montant_total' => $montantTotal,
            'statut' => 'calcule'
        ]);

        return $paiement;
    }

    /**
     * Calculate payments for all members of a cooperative for a specific period.
     */
    public static function calculerPaiementsCooperative($cooperativeId, $startDate, $endDate, $prixUnitaire)
    {
        $cooperative = Cooperative::find($cooperativeId);
        if (!$cooperative) {
            throw new \Exception('Coopérative introuvable');
        }

        $paiements = [];
        $membresActifs = $cooperative->membresActifs;

        foreach ($membresActifs as $membre) {
            $paiement = self::calculerPaiement(
                $membre->id_membre,
                $cooperativeId,
                $startDate,
                $endDate,
                $prixUnitaire
            );
            
            if ($paiement->quantite_totale > 0) {
                $paiements[] = $paiement;
            }
        }

        return $paiements;
    }

    /**
     * Get total amount by member.
     */
    public static function getTotalByMembre($membreId, $startDate = null, $endDate = null)
    {
        $query = self::where('id_membre', $membreId);
        
        if ($startDate && $endDate) {
            $query->where('periode_debut', '>=', $startDate)
                  ->where('periode_fin', '<=', $endDate);
        }
        
        return $query->sum('montant_total');
    }

    /**
     * Get total amount by cooperative.
     */
    public static function getTotalByCooperative($cooperativeId, $startDate = null, $endDate = null)
    {
        $query = self::where('id_cooperative', $cooperativeId);
        
        if ($startDate && $endDate) {
            $query->where('periode_debut', '>=', $startDate)
                  ->where('periode_fin', '<=', $endDate);
        }
        
        return $query->sum('montant_total');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paiement) {
            // Calculate montant_total automatically
            $paiement->montant_total = $paiement->quantite_totale * $paiement->prix_unitaire;
        });

        static::updating(function ($paiement) {
            // Recalculate montant_total if quantity or price changed
            if ($paiement->isDirty(['quantite_totale', 'prix_unitaire'])) {
                $paiement->montant_total = $paiement->quantite_totale * $paiement->prix_unitaire;
            }
        });
    }
}