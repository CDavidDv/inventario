<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'website',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    // Relaciones
    public function elementPrices(): HasMany
    {
        return $this->hasMany(ElementPrice::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function suppliedMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->where('type', 'in');
    }

    public function assignedItems(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'supplier_items')
                    ->withPivot(['is_primary_supplier', 'preferred_stock_level', 'priority', 'notes', 'is_active'])
                    ->withTimestamps();
    }

    public function activeAssignedItems(): BelongsToMany
    {
        return $this->assignedItems()->wherePivot('is_active', true);
    }

    public function primaryItems(): BelongsToMany
    {
        return $this->assignedItems()->wherePivot('is_primary_supplier', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Métodos auxiliares
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function deactivate()
    {
        $this->update(['status' => 'inactive']);
    }

    public function toggleStatus()
    {
        $newStatus = $this->status === 'active' ? 'inactive' : 'active';
        $this->update(['status' => $newStatus]);
    }

    // Métodos auxiliares para estadísticas
    public function getRecentSupplies($days = 30)
    {
        return $this->suppliedMovements()
                    ->with(['item', 'user'])
                    ->where('movement_date', '>=', now()->subDays($days))
                    ->orderBy('movement_date', 'desc')
                    ->get();
    }

    public function getTotalSuppliedValue($days = null)
    {
        $query = $this->suppliedMovements();
        
        if ($days) {
            $query->where('movement_date', '>=', now()->subDays($days));
        }
        
        return $query->sum('total_cost') ?? 0;
    }

    public function getSuppliedItemsCount($days = null)
    {
        $query = $this->suppliedMovements();
        
        if ($days) {
            $query->where('movement_date', '>=', now()->subDays($days));
        }
        
        return $query->distinct('component_id')->count();
    }

    public function getAveragePurchasePrice()
    {
        return $this->elementPrices()
                    ->whereNotNull('purchase_price')
                    ->avg('purchase_price') ?? 0;
    }

    public function getAverageSellingPrice()
    {
        return $this->elementPrices()
                    ->whereNotNull('selling_price')
                    ->avg('selling_price') ?? 0;
    }

    public function getTotalInventoryValue()
    {
        return $this->elementPrices()
                    ->join('items', 'element_prices.item_id', '=', 'items.id')
                    ->whereNotNull('element_prices.purchase_price')
                    ->selectRaw('SUM(items.current_stock * element_prices.purchase_price) as total')
                    ->value('total') ?? 0;
    }
}