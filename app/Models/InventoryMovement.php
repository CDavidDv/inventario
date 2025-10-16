<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'component_id',
        'supplier_id',
        'type',
        'concept',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'total_cost',
        'notes',
        'reference',
        'batch_number',
        'related_kit_id',
        'related_movement_id',
        'metadata',
        'movement_date',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'metadata' => 'array',
        'movement_date' => 'datetime',
        'approved_at' => 'datetime'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'component_id');
    }

    // Alias para item (para compatibilidad)
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'component_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function relatedKit(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'related_kit_id');
    }

    public function relatedMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'related_movement_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByBatch($query, $batch)
    {
        return $query->where('batch_number', $batch);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    public function getTypeLabel(): string
    {
        switch($this->type) {
            case 'in':
                return 'Entrada';
            case 'out':
                return 'Salida';
            case 'adjustment':
                return 'Ajuste';
            case 'transfer':
                return 'Transferencia';
            case 'assembly':
                return 'Ensamble';
            case 'production':
                return 'Producción';
            case 'sale':
                return 'Venta';
            case 'return':
                return 'Devolución';
            case 'loss':
                return 'Pérdida';
            default:
                return $this->type;
        }
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function needsApproval(): bool
    {
        // Movimientos que requieren aprobación (puedes personalizar esta lógica)
        $typesNeedingApproval = ['adjustment', 'loss', 'transfer'];
        return in_array($this->type, $typesNeedingApproval) && !$this->isApproved();
    }

    public function getImpactAttribute(): string
    {
        return $this->quantity > 0 ? 'positive' : 'negative';
    }
}
