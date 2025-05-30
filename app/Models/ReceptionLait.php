<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceptionLait extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'receptions_lait';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_reception';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_cooperative',
        'id_membre',
        'matricule_reception',
        'date_reception',
        'quantite_litres',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_reception' => 'date',
            'quantite_litres' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the cooperative that owns the reception.
     */
    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'id_cooperative', 'id_cooperative');
    }

    /**
     * Get the member that delivered the milk.
     */
    public function membre()
    {
        return $this->belongsTo(MembreEleveur::class, 'id_membre', 'id_membre');
    }

    /**
     * Scope a query to filter by cooperative.
     */
    public function scopeByCooperative($query, $cooperativeId)
    {
        return $query->where('id_cooperative', $cooperativeId);
    }

    /**
     * Scope a query to filter by member.
     */
    public function scopeByMembre($query, $membreId)
    {
        return $query->where('id_membre', $membreId);
    }

    /**
     * Scope a query to filter by date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date_reception', $date);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_reception', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by month and year.
     */
    public function scopeByMonth($query, $month, $year)
    {
        return $query->whereMonth('date_reception', $month)
                    ->whereYear('date_reception', $year);
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('date_reception', $year);
    }

    /**
     * Scope a query to get today's receptions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date_reception', today());
    }

    /**
     * Scope a query to get this week's receptions.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('date_reception', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope a query to get this month's receptions.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date_reception', now()->month)
                    ->whereYear('date_reception', now()->year);
    }

    /**
     * Scope a query to order by date (most recent first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('date_reception', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by date (oldest first).
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('date_reception', 'asc')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Get the total quantity for a specific cooperative.
     */
    public static function getTotalQuantiteByCooperative($cooperativeId, $startDate = null, $endDate = null)
    {
        $query = self::where('id_cooperative', $cooperativeId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('date_reception', [$startDate, $endDate]);
        }
        
        return $query->sum('quantite_litres');
    }

    /**
     * Get the total quantity for a specific member.
     */
    public static function getTotalQuantiteByMembre($membreId, $startDate = null, $endDate = null)
    {
        $query = self::where('id_membre', $membreId);
        
        if ($startDate && $endDate) {
            $query->whereBetween('date_reception', [$startDate, $endDate]);
        }
        
        return $query->sum('quantite_litres');
    }

    /**
     * Generate a unique matricule for reception.
     */
    public static function generateMatricule()
    {
        $year = date('Y');
        $prefix = 'REC' . $year;
        
        // Get the last matricule for this year
        $lastReception = self::where('matricule_reception', 'like', $prefix . '%')
                           ->orderBy('matricule_reception', 'desc')
                           ->first();
        
        if ($lastReception) {
            // Extract the number part and increment
            $lastNumber = (int) substr($lastReception->matricule_reception, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get formatted matricule with date.
     */
    public function getFullMatriculeAttribute()
    {
        return $this->matricule_reception . ' - ' . $this->date_reception->format('d/m/Y');
    }

    /**
     * Get formatted quantity with unit.
     */
    public function getQuantiteFormatteeAttribute()
    {
        return number_format($this->quantite_litres, 2) . ' L';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reception) {
            if (empty($reception->matricule_reception)) {
                $reception->matricule_reception = self::generateMatricule();
            }
        });
    }
}