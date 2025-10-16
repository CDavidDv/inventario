<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemItems extends Model
{
    use HasFactory;

    protected $table = 'items_items';

    protected $fillable = [
        'item_id',
        'item_id_2',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];

    // Relación con el kit
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    // Relación con el componente
    public function component(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id_2', 'id');
    }

    // Alias para item (usado en el código existente)
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id_2', 'id');
    }

    // Alias para quantity_required
    public function getQuantityRequiredAttribute()
    {
        return $this->quantity;
    }
}
