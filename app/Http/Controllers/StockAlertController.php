<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StockAlertController extends Controller
{
    /**
     * Obtener alertas de stock para notificaciones
     */
    public function getNotifications(): JsonResponse
    {
        try {
            // Obtener alertas de stock no leídas y no resueltas
            $alerts = StockAlert::with(['item'])
                ->unread()
                ->unresolved()
                ->limit(20)
                ->get();

            // También verificar items con stock bajo directamente
            $lowStockItems = Item::whereRaw('current_stock <= min_stock')
                ->where('current_stock', '>', 0)
                ->where('active', true)
                ->with('category')
                ->get();

            // Crear notificaciones para items con stock bajo
            $notifications = collect();

            // Agregar alertas existentes
            foreach ($alerts as $alert) {
                $notifications->push([
                    'id' => 'alert_' . $alert->id,
                    'type' => 'stock_alert',
                    'title' => $this->getAlertTitle($alert->alert_type),
                    'message' => $alert->message,
                    'data' => [
                        'item_id' => $alert->item_id,
                        'item' => $alert->item,
                        'alert_type' => $alert->alert_type,
                        'alert_id' => $alert->id
                    ],
                    'read' => $alert->is_read,
                    'created_at' => $alert->date->toISOString(),
                    'priority' => $alert->getPriorityAttribute()
                ]);
            }

            // Agregar items con stock bajo
            foreach ($lowStockItems as $item) {
                $notifications->push([
                    'id' => 'item_' . $item->id,
                    'type' => 'stock_alert',
                    'title' => 'Stock Bajo',
                    'message' => "{$item->name} tiene stock bajo ({$item->current_stock}/{$item->min_stock})",
                    'data' => [
                        'item_id' => $item->id,
                        'item' => $item,
                        'current_stock' => $item->current_stock,
                        'min_stock' => $item->min_stock
                    ],
                    'read' => false,
                    'created_at' => now()->toISOString(),
                    'priority' => $item->current_stock == 0 ? 1 : ($item->current_stock <= $item->min_stock * 0.5 ? 2 : 3)
                ]);
            }

            // Ordenar por prioridad y fecha
            $sortedNotifications = $notifications->sortBy([
                ['priority', 'asc'],
                ['created_at', 'desc']
            ])->values();

            return response()->json([
                'success' => true,
                'data' => $sortedNotifications
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo notificaciones de stock: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notificaciones'
            ], 500);
        }
    }

    /**
     * Marcar una alerta como leída
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        try {
            $alert = StockAlert::findOrFail($id);
            $alert->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Alerta marcada como leída'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar alerta como leída'
            ], 500);
        }
    }

    /**
     * Marcar todas las alertas como leídas
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            StockAlert::unread()->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Todas las alertas marcadas como leídas'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar alertas como leídas'
            ], 500);
        }
    }

    /**
     * Resolver una alerta
     */
    public function resolve(Request $request, $id): JsonResponse
    {
        try {
            $alert = StockAlert::findOrFail($id);
            $alert->markAsResolved();

            return response()->json([
                'success' => true,
                'message' => 'Alerta resuelta'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al resolver alerta'
            ], 500);
        }
    }

    /**
     * Obtener título de alerta según tipo
     */
    private function getAlertTitle(string $type): string
    {
        switch ($type) {
            case 'Agotado':
                return 'Stock Agotado';
            case 'Crítico':
                return 'Stock Crítico';
            case 'Mínimo':
                return 'Stock Mínimo';
            case 'Máximo':
                return 'Stock Máximo';
            default:
                return 'Alerta de Stock';
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
