<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WorkerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:worker_access');
    }

    public function dashboard()
    {
        $stats = [
            'total_components' => Item::where('active', true)->count(),
            'low_stock_items' => Item::where('current_stock', '<=', 'min_stock')
                ->where('active', true)
                ->count(),
            'movements_today' => InventoryMovement::whereDate('created_at', today())
                ->where('user_id', auth()->id())
                ->count(),
            'total_value' => Item::where('active', true)
                ->sum(DB::raw('current_stock * purchase_cost'))
        ];

        $lowStockComponents = Item::with(['category'])
            ->where('current_stock', '<=', 'min_stock')
            ->where('active', true)
            ->orderBy('current_stock', 'asc')
            ->limit(10)
            ->get();

        $recentMovements = InventoryMovement::with(['item'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Worker/Dashboard', [
            'stats' => $stats,
            'lowStockComponents' => $lowStockComponents,
            'recentMovements' => $recentMovements
        ]);
    }

    public function inventory()
    {
        $components = Item::with(['category'])
            ->where('active', true)
            ->orderBy('name')
            ->paginate(20);

        $concepts = $this->getConcepts();

        return Inertia::render('Worker/Inventory', [
            'components' => $components,
            'concepts' => $concepts
        ]);
    }

    public function addStock(Request $request)
    {
        $validated = $request->validate([
            'component_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'concept' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $component = Item::findOrFail($validated['component_id']);
        
        if (!$component->active) {
            return back()->with('error', 'No se puede modificar un componente inactivo');
        }

        DB::transaction(function () use ($validated, $component) {
            $quantityBefore = $component->current_stock;
            $quantityAfter = $quantityBefore + $validated['quantity'];

            // Actualizar stock del componente
            $component->update(['current_stock' => $quantityAfter]);

            // Registrar movimiento
            InventoryMovement::create([
                'item_id' => $component->id,
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'concept' => $validated['concept'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'user_id' => auth()->id(),
                'ip_address' => request()->ip()
            ]);
        });

        return back()->with('success', 'Stock agregado exitosamente');
    }

    public function removeStock(Request $request)
    {
        $validated = $request->validate([
            'component_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'concept' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $component = Item::findOrFail($validated['component_id']);
        
        if (!$component->active) {
            return back()->with('error', 'No se puede modificar un componente inactivo');
        }

        if ($component->current_stock < $validated['quantity']) {
            return back()->with('error', 'No hay suficiente stock disponible');
        }

        DB::transaction(function () use ($validated, $component) {
            $quantityBefore = $component->current_stock;
            $quantityAfter = $quantityBefore - $validated['quantity'];

            // Actualizar stock del componente
            $component->update(['current_stock' => $quantityAfter]);

            // Registrar movimiento
            InventoryMovement::create([
                'item_id' => $component->id,
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'concept' => $validated['concept'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'user_id' => auth()->id(),
                'ip_address' => request()->ip()
            ]);
        });

        return back()->with('success', 'Stock removido exitosamente');
    }

    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'component_id' => 'required|exists:items,id',
            'new_quantity' => 'required|integer|min:0',
            'concept' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $component = Item::findOrFail($validated['component_id']);
        
        if (!$component->active) {
            return back()->with('error', 'No se puede modificar un componente inactivo');
        }

        DB::transaction(function () use ($validated, $component) {
            $quantityBefore = $component->current_stock;
            $quantityAfter = $validated['new_quantity'];
            $difference = $quantityAfter - $quantityBefore;

            // Actualizar stock del componente
            $component->update(['current_stock' => $quantityAfter]);

            // Registrar movimiento
            InventoryMovement::create([
                'item_id' => $component->id,
                'type' => $difference > 0 ? 'adjustment_in' : 'adjustment_out',
                'quantity' => abs($difference),
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'concept' => $validated['concept'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'user_id' => auth()->id(),
                'ip_address' => request()->ip()
            ]);
        });

        return back()->with('success', 'Stock ajustado exitosamente');
    }

    public function movements()
    {
        $movements = InventoryMovement::with(['item'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return Inertia::render('Worker/Movements', [
            'movements' => $movements
        ]);
    }

    public function getConcepts()
    {
        return [
            'venta' => 'Venta',
            'armado' => 'Armado de Kit',
            'dañado' => 'Dañado/Defectuoso',
            'ajuste' => 'Ajuste de Inventario',
            'transferencia' => 'Transferencia',
            'devolucion' => 'Devolución',
            'perdida' => 'Pérdida',
            'donacion' => 'Donación',
            'mantenimiento' => 'Mantenimiento',
            'otro' => 'Otro'
        ];
    }

    public function searchComponents(Request $request)
    {
        $search = $request->get('search', '');
        
        $components = Item::with(['category'])
            ->where('active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($components);
    }

    public function getComponentDetails($id)
    {
        $component = Item::with(['category'])
            ->where('id', $id)
            ->where('active', true)
            ->first();

        if (!$component) {
            return response()->json(['error' => 'Componente no encontrado'], 404);
        }

        return response()->json($component);
    }
}
