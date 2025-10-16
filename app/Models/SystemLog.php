<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SystemLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'session_id',
        'loggable_type',
        'loggable_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getActionLabelAttribute(): string
    {
        $labels = [
            'create' => 'Crear',
            'update' => 'Actualizar',
            'delete' => 'Eliminar',
            'login' => 'Iniciar Sesión',
            'logout' => 'Cerrar Sesión',
            'view' => 'Ver',
            'export' => 'Exportar',
            'import' => 'Importar',
            'restore' => 'Restaurar',
            'force_delete' => 'Eliminar Permanentemente',
        ];

        return $labels[$this->action] ?? ucfirst($this->action);
    }

    public function getModuleLabelAttribute(): string
    {
        $labels = [
            'user' => 'Usuario',
            'item' => 'Item',
            'category' => 'Categoría',
            'supplier' => 'Proveedor',
            'inventory' => 'Inventario',
            'production' => 'Producción',
            'auth' => 'Autenticación',
            'system' => 'Sistema',
        ];

        return $labels[$this->module] ?? ucfirst($this->module);
    }

    public static function logAction(string $action, string $module, string $description, $loggable = null, $oldValues = null, $newValues = null): void
    {
        if (!auth()->check()) {
            return;
        }

        static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'loggable_type' => $loggable ? get_class($loggable) : null,
            'loggable_id' => $loggable ? $loggable->id : null,
        ]);
    }
}
