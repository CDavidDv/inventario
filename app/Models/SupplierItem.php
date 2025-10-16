<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'item_id',
        'is_primary_supplier',
        'preferred_stock_level',
        'priority',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'is_primary_supplier' => 'boolean',
        'preferred_stock_level' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relaciones
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary_supplier', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority');
    }
}
