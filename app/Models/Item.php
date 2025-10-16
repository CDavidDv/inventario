<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'category_id',
        'description',
        'unit',
        'min_stock',
        'max_stock',
        'current_stock',
        'purchase_cost',
        'sale_price',
        'location',
        'serial_number',
        'purchase_date',
        'purchase_price',
        'active'
    ];

    protected $casts = [
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'purchase_cost' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'active' => 'boolean'
    ];

    // Relaciones
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Relaciones jerárquicas
    // Un kit tiene muchos componentes/elementos
    public function components(): HasMany
    {
        return $this->hasMany(ItemItems::class, 'item_id', 'id');
    }

    // Alias para assignedItems (usado en el código existente)
    public function assignedItems(): HasMany
    {
        return $this->hasMany(ItemItems::class, 'item_id', 'id');
    }

    // Un componente/elemento pertenece a muchos kits
    public function belongsToKits(): HasMany
    {
        return $this->hasMany(ItemItems::class, 'item_id_2', 'id');
    }

    // Métodos de conveniencia para obtener items relacionados
    public function getComponentsItems()
    {
        return $this->components()->with('component')->get()->pluck('component');
    }

    public function getKitsItems()
    {
        return $this->belongsToKits()->with('kit')->get()->pluck('kit');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'item_suppliers', 'item_id', 'supplier_id')
            ->withPivot('price', 'lead_time');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'component_id');
    }

    public function movementLogs(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'component_id');
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    public function inventoryAlerts(): HasMany
    {
        return $this->hasMany(InventoryAlert::class);
    }

    // Métodos de conveniencia
    public function getIsKitAttribute(): bool
    {
        return $this->type === 'kit';
    }

    public function getMinQuantityAttribute()
    {
        return $this->min_stock;
    }

    public function getMaxQuantityAttribute()
    {
        return $this->max_stock;
    }

    public function getCurrentQuantityAttribute()
    {
        return $this->current_stock;
    }

    public function getCostAttribute()
    {
        return $this->purchase_cost;
    }

    // Nuevo método para obtener el estado del stock
    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock < $this->min_stock) {
            return 'bajo_stock';
        } elseif ($this->current_stock == $this->min_stock) {
            return 'en_minimo';
        } elseif ($this->current_stock <= $this->max_stock) {
            return 'normal';
        } else {
            return 'sobre_stock';
        }
    }

    // Método para obtener el texto del estado
    public function getStockStatusTextAttribute(): string
    {
        switch($this->stock_status) {
            case 'bajo_stock':
                return 'Bajo Stock';
            case 'en_minimo':
                return 'En el Mínimo';
            case 'normal':
                return 'Normal';
            case 'sobre_stock':
                return 'Sobre Stock';
            default:
                return 'Desconocido';
        }
    }

    // Método para obtener el color del estado
    public function getStockStatusColorAttribute(): string
    {
        switch($this->stock_status) {
            case 'bajo_stock':
                return 'text-red-600 bg-red-50';
            case 'en_minimo':
                return 'text-yellow-600 bg-yellow-50';
            case 'normal':
                return 'text-green-600 bg-green-50';
            case 'sobre_stock':
                return 'text-blue-600 bg-blue-50';
            default:
                return 'text-gray-600 bg-gray-50';
        }
    }
}
