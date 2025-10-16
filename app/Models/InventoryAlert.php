<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAlert extends Model
{
    use HasFactory;

    protected $table = 'inventory_alert';

    protected $fillable = [
        'item_id',
        'type',
        'title',
        'message',
        'is_read',
        'read_at',
        'current_stock',
        'threshold_value',
        'priority',
        'is_active',
        'alert_date',
        'alert_price',
        'observation',
        'user_id'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function getAlertPrice(): float
    {
        return $this->alert_price;
    }

    public function getAlertDate(): string
    {
        return $this->alert_date;
    }
}