<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrixUnitaire extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prix_unitaires';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prix',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'prix' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope to order by most recent first.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to order by oldest first.
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * Get the current (most recent) price.
     */
    public static function getPrixActuel()
    {
        $dernierPrix = self::latest()->first();
        return $dernierPrix ? $dernierPrix->prix : 3.50; // Prix par défaut si aucun prix configuré
    }

    /**
     * Get the current price object.
     */
    public static function getDernierPrix()
    {
        return self::latest()->first();
    }

    /**
     * Create a new price entry.
     */
    public static function creerNouveauPrix($prix)
    {
        return self::create([
            'prix' => $prix
        ]);
    }

    /**
     * Get formatted price.
     */
    public function getPrixFormatteeAttribute()
    {
        return number_format($this->prix, 2) . ' DH';
    }

    /**
     * Get formatted price with unit.
     */
    public function getPrixAvecUniteAttribute()
    {
        return number_format($this->prix, 2) . ' DH/L';
    }

    /**
     * Get all historical prices with pagination.
     */
    public static function getHistorique($perPage = 10)
    {
        return self::latest()->paginate($perPage);
    }

    /**
     * Get price statistics.
     */
    public static function getStatistiques()
    {
        $prix = self::all();
        
        if ($prix->isEmpty()) {
            return [
                'total_modifications' => 0,
                'prix_actuel' => 3.50,
                'prix_minimum' => null,
                'prix_maximum' => null,
                'prix_moyen' => null,
                'derniere_modification' => null,
            ];
        }

        return [
            'total_modifications' => $prix->count(),
            'prix_actuel' => $prix->first()->prix,
            'prix_minimum' => $prix->min('prix'),
            'prix_maximum' => $prix->max('prix'),
            'prix_moyen' => $prix->avg('prix'),
            'derniere_modification' => $prix->first()->created_at,
        ];
    }

    /**
     * Check if this is the current price.
     */
    public function isCurrent()
    {
        $dernierPrix = self::latest()->first();
        return $dernierPrix && $this->id === $dernierPrix->id;
    }

    /**
     * Get formatted date.
     */
    public function getDateFormatteeAttribute()
    {
        return $this->created_at->format('d/m/Y à H:i');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prix) {
            // Validation que le prix est positif
            if ($prix->prix <= 0) {
                throw new \Exception('Le prix doit être supérieur à zéro');
            }
        });
    }
}
