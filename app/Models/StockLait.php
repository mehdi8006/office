<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StockLait extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_lait';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_stock';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_cooperative',
        'date_stock',
        'quantite_totale',
        'quantite_disponible',
        'quantite_livree',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_stock' => 'date',
            'quantite_totale' => 'decimal:2',
            'quantite_disponible' => 'decimal:2',
            'quantite_livree' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the cooperative that owns this stock.
     */
    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'id_cooperative', 'id_cooperative');
    }

    /**
     * Get all receptions for this stock date and cooperative.
     */
    public function receptions()
    {
        return $this->hasMany(ReceptionLait::class, 'id_cooperative', 'id_cooperative')
                    ->whereDate('date_reception', $this->date_stock);
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
        return $query->whereDate('date_stock', $date);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_stock', [$startDate, $endDate]);
    }

    /**
     * Scope a query to get today's stock.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date_stock', today());
    }

    /**
     * Scope a query to get this week's stock.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date_stock', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope a query to get this month's stock.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date_stock', now()->month)
                    ->whereYear('date_stock', now()->year);
    }

    /**
     * Scope to get stocks with available quantity > 0.
     */
    public function scopeWithAvailableStock($query)
    {
        return $query->where('quantite_disponible', '>', 0);
    }

    /**
     * Scope to get stocks that are fully delivered.
     */
    public function scopeFullyDelivered($query)
    {
        return $query->where('quantite_disponible', 0)
                    ->where('quantite_totale', '>', 0);
    }

    /**
     * Scope to order by date (most recent first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('date_stock', 'desc');
    }

    /**
     * Scope to order by date (oldest first).
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('date_stock', 'asc');
    }

    /**
     * Create or update stock for a specific cooperative and date.
     */
    public static function updateDailyStock($cooperativeId, $date = null)
    {
        $date = $date ?? today();
        
        // Calculate total quantity from receptions for this date
        $totalQuantite = ReceptionLait::where('id_cooperative', $cooperativeId)
                                    ->whereDate('date_reception', $date)
                                    ->sum('quantite_litres');

        // Find or create stock record
        $stock = self::firstOrNew([
            'id_cooperative' => $cooperativeId,
            'date_stock' => $date
        ]);

        // Update quantities
        $quantiteLivree = $stock->quantite_livree ?? 0;
        $stock->quantite_totale = $totalQuantite;
        $stock->quantite_disponible = $totalQuantite - $quantiteLivree;
        
        $stock->save();
        
        return $stock;
    }

    /**
     * Process a delivery (reduce available stock).
     */
    public function livrer($quantite)
    {
        if ($quantite > $this->quantite_disponible) {
            throw new \Exception("Quantité à livrer ({$quantite}L) supérieure au stock disponible ({$this->quantite_disponible}L)");
        }

        $this->quantite_livree += $quantite;
        $this->quantite_disponible -= $quantite;
        
        return $this->save();
    }

    /**
     * Cancel a delivery (increase available stock).
     */
    public function annulerLivraison($quantite)
    {
        if ($quantite > $this->quantite_livree) {
            throw new \Exception("Quantité à annuler ({$quantite}L) supérieure à la quantité livrée ({$this->quantite_livree}L)");
        }

        $this->quantite_livree -= $quantite;
        $this->quantite_disponible += $quantite;
        
        return $this->save();
    }

    /**
     * Check if there's available stock.
     */
    public function hasAvailableStock()
    {
        return $this->quantite_disponible > 0;
    }

    /**
     * Check if all stock is delivered.
     */
    public function isFullyDelivered()
    {
        return $this->quantite_disponible == 0 && $this->quantite_totale > 0;
    }

    /**
     * Get percentage of stock delivered.
     */
    public function getPercentageLivreAttribute()
    {
        if ($this->quantite_totale == 0) {
            return 0;
        }
        
        return round(($this->quantite_livree / $this->quantite_totale) * 100, 2);
    }

    /**
     * Get percentage of stock available.
     */
    public function getPercentageDisponibleAttribute()
    {
        if ($this->quantite_totale == 0) {
            return 0;
        }
        
        return round(($this->quantite_disponible / $this->quantite_totale) * 100, 2);
    }

    /**
     * Get formatted quantities.
     */
    public function getQuantiteTotaleFormatteeAttribute()
    {
        return number_format($this->quantite_totale, 2) . ' L';
    }

    public function getQuantiteDisponibleFormatteeAttribute()
    {
        return number_format($this->quantite_disponible, 2) . ' L';
    }

    public function getQuantiteLivreeFormatteeAttribute()
    {
        return number_format($this->quantite_livree, 2) . ' L';
    }

    /**
     * Get stock status.
     */
    public function getStatutStockAttribute()
    {
        if ($this->quantite_totale == 0) {
            return 'Aucun stock';
        } elseif ($this->quantite_disponible == 0) {
            return 'Entièrement livré';
        } elseif ($this->quantite_livree == 0) {
            return 'Non livré';
        } else {
            return 'Partiellement livré';
        }
    }

    /**
     * Get stock status color for UI.
     */
    public function getStatutColorAttribute()
    {
        if ($this->quantite_totale == 0) {
            return 'secondary';
        } elseif ($this->quantite_disponible == 0) {
            return 'success';
        } elseif ($this->quantite_livree == 0) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * Get total stock for all cooperatives on a specific date.
     */
    public static function getTotalStockByDate($date)
    {
        return self::whereDate('date_stock', $date)->sum('quantite_totale');
    }

    /**
     * Get total available stock for all cooperatives on a specific date.
     */
    public static function getTotalAvailableByDate($date)
    {
        return self::whereDate('date_stock', $date)->sum('quantite_disponible');
    }

    /**
     * Get total delivered stock for all cooperatives on a specific date.
     */
    public static function getTotalDeliveredByDate($date)
    {
        return self::whereDate('date_stock', $date)->sum('quantite_livree');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stock) {
            // Ensure quantities are consistent
            if ($stock->quantite_disponible + $stock->quantite_livree != $stock->quantite_totale) {
                $stock->quantite_disponible = $stock->quantite_totale - $stock->quantite_livree;
            }
        });

        static::updating(function ($stock) {
            // Ensure quantities are consistent
            if ($stock->quantite_disponible + $stock->quantite_livree != $stock->quantite_totale) {
                $stock->quantite_disponible = $stock->quantite_totale - $stock->quantite_livree;
            }
        });
    }
}