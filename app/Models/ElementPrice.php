<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElementPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'supplier_id',
        'purchase_price',
        'selling_price',
        'currency',
        'minimum_quantity',
        'lead_time_days',
        'notes',
        'valid_from',
        'valid_until',
        'is_active',
        'is_preferred'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'minimum_quantity' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
        'is_preferred' => 'boolean'
    ];

    // Relaciones
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    public function scopeValidNow($query)
    {
        $now = now()->toDateString();
        return $query->where(function($q) use ($now) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', $now);
        });
    }

    // Métodos auxiliares
    public function isValid(): bool
    {
        $now = now()->toDateString();
        $validFrom = !$this->valid_from || $this->valid_from <= $now;
        $validUntil = !$this->valid_until || $this->valid_until >= $now;
        
        return $this->is_active && $validFrom && $validUntil;
    }

    public function getMarginAttribute(): float
    {
        if (!$this->purchase_price || !$this->selling_price || $this->purchase_price == 0) {
            return 0;
        }
        
        return (($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100;
    }

    public function getProfitAttribute(): float
    {
        return ($this->selling_price ?? 0) - ($this->purchase_price ?? 0);
    }
}
