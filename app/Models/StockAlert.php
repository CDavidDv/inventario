<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'alert_type',
        'message',
        'date',
        'is_read',
        'is_resolved',
        'resolved_at'
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_read' => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime'
    ];

    // Relaciones
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    public function scopeCritical($query)
    {
        return $query->whereIn('alert_type', ['Agotado', 'Crítico']);
    }

    // Métodos auxiliares
    public function markAsRead(): bool
    {
        return $this->update(['is_read' => true]);
    }

    public function markAsResolved(): bool
    {
        return $this->update([
            'is_resolved' => true,
            'resolved_at' => now()
        ]);
    }

    public function getAlertTypeColorAttribute(): string
    {
        switch ($this->alert_type) {
            case 'Agotado':
                return 'red';
            case 'Crítico':
                return 'orange';
            case 'Mínimo':
                return 'yellow';
            case 'Máximo':
                return 'blue';
            default:
                return 'gray';
        }
    }

    public function getAlertTypeIconAttribute(): string
    {
        switch ($this->alert_type) {
            case 'Agotado':
                return 'exclamation-triangle';
            case 'Crítico':
                return 'exclamation-circle';
            case 'Mínimo':
                return 'arrow-down';
            case 'Máximo':
                return 'arrow-up';
            default:
                return 'information-circle';
        }
    }

    public function getPriorityAttribute(): int
    {
        switch ($this->alert_type) {
            case 'Agotado':
                return 1;
            case 'Crítico':
                return 2;
            case 'Mínimo':
                return 3;
            case 'Máximo':
                return 4;
            default:
                return 5;
        }
    }

    public static function createAlert(
        int $itemId,
        string $alertType,
        string $message
    ): self {
        // Evitar duplicados recientes (últimas 24 horas)
        $existing = self::where('item_id', $itemId)
            ->where('alert_type', $alertType)
            ->where('is_resolved', false)
            ->where('date', '>=', now()->subDay())
            ->first();

        if ($existing) {
            return $existing;
        }

        return self::create([
            'item_id' => $itemId,
            'alert_type' => $alertType,
            'message' => $message,
            'date' => now(),
            'is_read' => false,
            'is_resolved' => false
        ]);
    }
}
