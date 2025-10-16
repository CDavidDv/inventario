<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\Item;
use App\Models\InventoryMovement;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Production::with(['kit', 'creator']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('kit', function($kitQuery) use ($search) {
                      $kitQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kit_id')) {
            $query->where('kit_id', $request->kit_id);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $productions = $query->paginate(15)->withQueryString();

        // Obtener kits disponibles para el filtro
        $kits = Item::where('type', 'kit')->select('id', 'name')->get();

        // Estadísticas
        $stats = [
            'total' => Production::count(),
            'pending' => Production::where('status', 'pending')->count(),
            'in_progress' => Production::where('status', 'in_progress')->count(),
            'completed' => Production::where('status', 'completed')->count(),
            'cancelled' => Production::where('status', 'cancelled')->count(),
        ];

        return Inertia::render('Produccion/Index', [
            'productions' => $productions,
            'kits' => $kits,
            'filters' => $request->only(['search', 'status', 'kit_id', 'sort', 'direction']),
            'stats' => $stats
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kits = Item::where('type', 'kit')
            ->where('is_active', true)
            ->with(['assignedItems.item'])
            ->select('id', 'name', 'description', 'current_stock')
            ->get();

        return Inertia::render('Produccion/Create', [
            'kits' => $kits
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'kit_id' => 'required|exists:items,id',
            'quantity_requested' => 'required|integer|min:1',
            'due_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string'
        ]);

        // Verificar que el kit_id corresponde a un kit
        $kit = Item::where('id', $validated['kit_id'])
            ->where('type', 'kit')
            ->first();

        if (!$kit) {
            return back()->withErrors(['kit_id' => 'El elemento seleccionado no es un kit válido.']);
        }

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'pending';

        $production = Production::create($validated);

        return redirect()->route('production.index')
            ->with('success', 'Orden de producción creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Production $production)
    {
        $production->load([
            'kit.assignedItems.item',
            'creator'
        ]);

        // Calcular componentes necesarios y disponibles
        $componentsStatus = [];
        if ($production->kit && $production->kit->assignedItems) {
            foreach ($production->kit->assignedItems as $assignment) {
                $component = $assignment->item;
                $neededQuantity = $assignment->quantity * $production->quantity_requested;
                $availableQuantity = $component->current_stock;
                
                $componentsStatus[] = [
                    'component' => $component,
                    'needed_per_kit' => $assignment->quantity,
                    'total_needed' => $neededQuantity,
                    'available' => $availableQuantity,
                    'sufficient' => $availableQuantity >= $neededQuantity
                ];
            }
        }

        $stats = [
            'total' => Production::count(),
            'pending' => Production::where('status', 'pending')->count(),
            'in_progress' => Production::where('status', 'in_progress')->count(),
            'completed' => Production::where('status', 'completed')->count(),
            'cancelled' => Production::where('status', 'cancelled')->count(),
        ];

        $recentSupplies = InventoryMovement::where('supplier_id', $production->id)
            ->where('type', 'in')
            ->with('item', 'user')
            ->orderBy('movement_date', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Produccion/Show', [
            'production' => $production,
            'componentsStatus' => $componentsStatus,
            'recentSupplies' => $recentSupplies,
            'stats' => $stats
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Production $production)
    {
        // Solo se pueden editar las órdenes pendientes
        if ($production->status !== 'pending') {
            return redirect()->route('production.show', $production)
                ->with('error', 'Solo se pueden editar las órdenes de producción pendientes.');
        }

        $production->load('kit');
        
        $kits = Item::where('type', 'kit')
            ->where('is_active', true)
            ->select('id', 'name', 'description', 'current_stock')
            ->get();

        return Inertia::render('Produccion/Edit', [
            'production' => $production,
            'kits' => $kits
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Production $production)
    {
        // Solo se pueden editar las órdenes pendientes
        if ($production->status !== 'pending') {
            return back()->withErrors(['error' => 'Solo se pueden editar las órdenes de producción pendientes.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'kit_id' => 'required|exists:items,id',
            'quantity_requested' => 'required|integer|min:1',
            'due_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string'
        ]);

        // Verificar que el kit_id corresponde a un kit
        $kit = Item::where('id', $validated['kit_id'])
            ->where('type', 'kit')
            ->first();

        if (!$kit) {
            return back()->withErrors(['kit_id' => 'El elemento seleccionado no es un kit válido.']);
        }

        $production->update($validated);

        return redirect()->route('production.index')
            ->with('success', 'Orden de producción actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Production $production)
    {
        // Solo se pueden eliminar las órdenes pendientes o canceladas
        if (!in_array($production->status, ['pending', 'cancelled'])) {
            return back()->withErrors(['error' => 'Solo se pueden eliminar las órdenes pendientes o canceladas.']);
        }

        $productionName = $production->name;
        $production->delete();

        return redirect()->route('production.index')
            ->with('success', "Orden de producción '{$productionName}' eliminada exitosamente.");
    }

    /**
     * Start production
     */
    public function start(Production $production)
    {
        if (!$production->canBeStarted()) {
            return back()->withErrors(['error' => 'Esta orden de producción no puede ser iniciada.']);
        }

        // Verificar disponibilidad de componentes
        $production->load('kit.assignedItems.item');
        
        if ($production->kit && $production->kit->assignedItems) {
            foreach ($production->kit->assignedItems as $assignment) {
                $component = $assignment->item;
                $neededQuantity = $assignment->quantity * $production->quantity_requested;
                
                if ($component->current_stock < $neededQuantity) {
                    return back()->withErrors([
                        'error' => "Stock insuficiente del componente '{$component->name}'. Necesario: {$neededQuantity}, Disponible: {$component->current_stock}"
                    ]);
                }
            }
        }

        $production->update([
            'status' => 'in_progress',
            'start_date' => now()
        ]);

        return back()->with('success', 'Orden de producción iniciada exitosamente.');
    }

    /**
     * Complete production
     */
    public function complete(Request $request, Production $production)
    {
        if ($production->status !== 'in_progress') {
            return back()->withErrors(['error' => 'Esta orden de producción no está en progreso.']);
        }

        $validated = $request->validate([
            'quantity_produced' => 'required|integer|min:1|max:' . $production->quantity_requested,
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($production, $validated) {
            $production->load('kit.assignedItems.item');

            // Reducir componentes del inventario
            $componentsUsed = [];
            if ($production->kit && $production->kit->assignedItems) {
                foreach ($production->kit->assignedItems as $assignment) {
                    $component = $assignment->item;
                    $usedQuantity = $assignment->quantity * $validated['quantity_produced'];
                    
                    // Reducir stock del componente
                    $component->decrement('current_stock', $usedQuantity);
                    
                    // Registrar movimiento de inventario
                    InventoryMovement::create([
                        'item_id' => $component->id,
                        'type' => 'decrease',
                        'quantity' => $usedQuantity,
                        'reason' => 'production_use',
                        'notes' => "Usado en producción: {$production->name}",
                        'user_id' => Auth::id()
                    ]);
                    
                    $componentsUsed[] = [
                        'item_id' => $component->id,
                        'item_name' => $component->name,
                        'quantity_used' => $usedQuantity
                    ];
                }
            }

            // Aumentar stock del kit producido
            $production->kit->increment('current_stock', $validated['quantity_produced']);
            
            // Registrar movimiento de inventario del kit
            InventoryMovement::create([
                'item_id' => $production->kit->id,
                'type' => 'increase',
                'quantity' => $validated['quantity_produced'],
                'reason' => 'production_completed',
                'notes' => "Producción completada: {$production->name}",
                'user_id' => Auth::id()
            ]);

            // Actualizar la orden de producción
            $production->update([
                'status' => 'completed',
                'quantity_produced' => $validated['quantity_produced'],
                'end_date' => now(),
                'components_used' => $componentsUsed,
                'notes' => $validated['notes'] ?? $production->notes
            ]);
        });

        return redirect()->route('production.show', $production)
            ->with('success', 'Producción completada exitosamente.');
    }

    /**
     * Cancel production
     */
    public function cancel(Production $production)
    {
        if ($production->isCompleted()) {
            return back()->withErrors(['error' => 'No se puede cancelar una producción ya completada.']);
        }

        $production->update([
            'status' => 'cancelled',
            'end_date' => now()
        ]);

        return back()->with('success', 'Orden de producción cancelada.');
    }

    /**
     * Update production progress
     */
    public function updateProgress(Request $request, Production $production)
    {
        if ($production->status !== 'in_progress') {
            return back()->withErrors(['error' => 'Esta orden de producción no está en progreso.']);
        }

        $validated = $request->validate([
            'quantity_produced' => 'required|integer|min:0|max:' . $production->quantity_requested,
            'notes' => 'nullable|string'
        ]);

        $production->update($validated);

        return back()->with('success', 'Progreso actualizado exitosamente.');
    }

    /**
     * Agregar elementos individuales al inventario
     */
    public function addElement(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'supplier_id' => 'required|exists:supplier,id',
            'cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255'
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::findOrFail($validated['item_id']);

            // Verificar que sea un elemento individual
            if ($item->type !== 'element') {
                throw new \Exception('Solo se pueden agregar elementos individuales con este método.');
            }

            // Incrementar stock
            $item->increment('current_stock', $validated['quantity']);

            $supplier = \App\Models\Supplier::find($validated['supplier_id']);

            // Registrar movimiento de inventario
            InventoryMovement::create([
                'component_id' => $item->id,
                'type' => 'in',
                'concept' => 'manual_addition',
                'supplier_id' => $validated['supplier_id'],
                'quantity' => $validated['quantity'],
                'quantity_before' => $item->current_stock - $validated['quantity'],
                'quantity_after' => $item->current_stock,
                'unit_cost' => $validated['cost'],
                'total_cost' => $validated['quantity'] * $validated['cost'],
                'notes' => "Elemento agregado - Proveedor: " . $validated['supplier_id'] . "- " . ($supplier ? $supplier->name : 'N/A') .
                          " - Costo: $" . $validated['cost'] . 
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);

            // Actualizar costo de compra si es diferente
            if ($item->purchase_cost != $validated['cost']) {
                $item->update(['purchase_cost' => $validated['cost']]);
            }

            
        });

        return back()->with('success', 'Elemento agregado exitosamente al inventario.');
    }

    /**
     * Agregar componente al inventario (resta elementos existentes)
     */
    public function addComponent(Request $request)
    {
        $validated = $request->validate([
            'component_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255'
        ]);

        DB::transaction(function () use ($validated) {
            $component = Item::with('components.item')->findOrFail($validated['component_id']);

            // Verificar que sea un componente
            if ($component->type !== 'component') {
                throw new \Exception('El elemento seleccionado no es un componente.');
            }

            // Verificar disponibilidad de elementos
            foreach ($component->components as $assignment) {
                $element = $assignment->item;
                $neededQuantity = $assignment->quantity * $validated['quantity'];
                
                if ($element->current_stock < $neededQuantity) {
                    throw new \Exception("Stock insuficiente del elemento '{$element->name}'. Necesario: {$neededQuantity}, Disponible: {$element->current_stock}");
                }
            }

            // Procesar la adición del componente
            foreach ($component->components as $assignment) {
                $element = $assignment->item;
                $usedQuantity = $assignment->quantity * $validated['quantity'];
                
                // Reducir stock del elemento
                $element->decrement('current_stock', $usedQuantity);
                
                // Registrar movimiento
                InventoryMovement::create([
                    'component_id' => $element->id,
                    'type' => 'out',
                    'concept' => 'component_creation',
                    'quantity' => $usedQuantity,
                    'quantity_before' => $element->current_stock + $usedQuantity,
                    'quantity_after' => $element->current_stock,
                    'notes' => "Usado para crear {$validated['quantity']} unidad(es) del componente '{$component->name}'" . 
                              ($validated['notes'] ? " - " . $validated['notes'] : ''),
                    'created_by' => Auth::id(),
                    'movement_date' => now()
                ]);
            }

            // Aumentar stock del componente
            $component->increment('current_stock', $validated['quantity']);
            
            // Registrar movimiento del componente
            InventoryMovement::create([
                'component_id' => $component->id,
                'type' => 'in',
                'concept' => 'component_assembly',
                'quantity' => $validated['quantity'],
                'quantity_before' => $component->current_stock - $validated['quantity'],
                'quantity_after' => $component->current_stock,
                'notes' => "Componente ensamblado a partir de elementos" . 
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);
        });

        return back()->with('success', 'Componente agregado exitosamente al inventario.');
    }

    /**
     * Agregar kit al inventario (resta elementos y/o componentes)
     */
    public function addKit(Request $request)
    {
        $validated = $request->validate([
            'kit_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255'
        ]);

        DB::transaction(function () use ($validated) {
            $kit = Item::with('components.item')->findOrFail($validated['kit_id']);

            // Verificar que sea un kit
            if ($kit->type !== 'kit') {
                throw new \Exception('El elemento seleccionado no es un kit.');
            }

            // Verificar disponibilidad de componentes y elementos
            $itemsToProcess = [];
            foreach ($kit->components as $assignment) {
                $item = $assignment->item;
                $neededQuantity = $assignment->quantity * $validated['quantity'];
                
                if ($item->current_stock < $neededQuantity) {
                    throw new \Exception("Stock insuficiente del item '{$item->name}'. Necesario: {$neededQuantity}, Disponible: {$item->current_stock}");
                }
                
                $itemsToProcess[] = [
                    'item' => $item,
                    'quantity' => $neededQuantity,
                    'per_kit' => $assignment->quantity
                ];
            }

            // Procesar la adición del kit
            foreach ($itemsToProcess as $itemData) {
                $item = $itemData['item'];
                $usedQuantity = $itemData['quantity'];
                
                // Reducir stock del item
                $item->decrement('current_stock', $usedQuantity);
                
                // Registrar movimiento
                InventoryMovement::create([
                    'component_id' => $item->id,
                    'type' => 'out',
                    'concept' => 'kit_assembly',
                    'quantity' => $usedQuantity,
                    'quantity_before' => $item->current_stock + $usedQuantity,
                    'quantity_after' => $item->current_stock,
                    'notes' => "Usado para crear {$validated['quantity']} unidad(es) del kit '{$kit->name}'" . 
                              ($validated['notes'] ? " - " . $validated['notes'] : ''),
                    'created_by' => Auth::id(),
                    'movement_date' => now()
                ]);
            }

            // Aumentar stock del kit
            $kit->increment('current_stock', $validated['quantity']);
            
            // Registrar movimiento del kit
            InventoryMovement::create([
                'component_id' => $kit->id,
                'type' => 'in',
                'concept' => 'kit_assembly',
                'quantity' => $validated['quantity'],
                'quantity_before' => $kit->current_stock - $validated['quantity'],
                'quantity_after' => $kit->current_stock,
                'notes' => "Kit ensamblado a partir de componentes/elementos" . 
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);
        });

        return back()->with('success', 'Kit agregado exitosamente al inventario.');
    }

    /**
     * Obtener items disponibles por tipo para formularios
     */
    public function getAvailableItems($type)
    {
        $items = Item::where('type', $type)
            ->where('active', true)
            ->select('id', 'name', 'current_stock', 'unit')
            ->orderBy('name')
            ->get();

        return response()->json($items);
    }

    /**
     * Obtener proveedores activos
     */
    public function getActiveSuppliers()
    {
        $suppliers = \App\Models\Supplier::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($suppliers);
    }

    /**
     * Obtener componentes de un kit/componente específico
     */
    public function getItemComponents($itemId)
    {
        $item = Item::with(['components.item' => function($query) {
            $query->select('id', 'name', 'current_stock', 'unit', 'type');
        }])->findOrFail($itemId);

        $components = $item->components->map(function($assignment) {
            return [
                'item' => $assignment->item,
                'quantity_needed' => $assignment->quantity,
                'available_stock' => $assignment->item->current_stock,
                'sufficient' => $assignment->item->current_stock >= $assignment->quantity
            ];
        });

        return response()->json([
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $item->type
            ],
            'components' => $components
        ]);
    }

    /**
     * Marcar items como defectuosos
     */
    public function markAsDefective(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['quantity']) {
                throw new \Exception("Stock insuficiente. Disponible: {$item->current_stock}, Solicitado: {$validated['quantity']}");
            }

            // Reducir stock
            $item->decrement('current_stock', $validated['quantity']);

            // Registrar movimiento
            InventoryMovement::create([
                'component_id' => $item->id,
                'type' => 'out',
                'concept' => 'defective',
                'quantity' => $validated['quantity'],
                'quantity_before' => $item->current_stock + $validated['quantity'],
                'quantity_after' => $item->current_stock,
                'notes' => "Producto defectuoso - Razón: {$validated['reason']}" . 
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);
        });

        return back()->with('success', 'Items marcados como defectuosos exitosamente.');
    }

    /**
     * Marcar items como dañados
     */
    public function markAsDamaged(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'damage_type' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['quantity']) {
                throw new \Exception("Stock insuficiente. Disponible: {$item->current_stock}, Solicitado: {$validated['quantity']}");
            }

            // Reducir stock
            $item->decrement('current_stock', $validated['quantity']);

            // Registrar movimiento
            InventoryMovement::create([
                'component_id' => $item->id,
                'type' => 'out',
                'concept' => 'damaged',
                'quantity' => $validated['quantity'],
                'quantity_before' => $item->current_stock + $validated['quantity'],
                'quantity_after' => $item->current_stock,
                'notes' => "Producto dañado - Tipo: {$validated['damage_type']}" . 
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);
        });

        return back()->with('success', 'Items marcados como dañados exitosamente.');
    }

    /**
     * Registrar devolución
     */
    public function registerReturn(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'return_reason' => 'required|string|max:255',
            'customer_info' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['quantity']) {
                throw new \Exception("Stock insuficiente. Disponible: {$item->current_stock}, Solicitado: {$validated['quantity']}");
            }

            // Reducir stock
            $item->decrement('current_stock', $validated['quantity']);

            // Registrar movimiento
            InventoryMovement::create([
                'component_id' => $item->id,
                'type' => 'out',
                'concept' => 'return',
                'quantity' => $validated['quantity'],
                'quantity_before' => $item->current_stock + $validated['quantity'],
                'quantity_after' => $item->current_stock,
                'notes' => "Devolución - Razón: {$validated['return_reason']}" . 
                          ($validated['customer_info'] ? " - Cliente: {$validated['customer_info']}" : '') .
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);
        });

        return back()->with('success', 'Devolución registrada exitosamente.');
    }

    /**
     * Registrar venta por internet
     */
    public function registerInternetSale(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'sale_price' => 'required|numeric|min:0',
            'platform' => 'required|string|max:100',
            'order_number' => 'nullable|string|max:100',
            'customer_info' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['quantity']) {
                throw new \Exception("Stock insuficiente. Disponible: {$item->current_stock}, Solicitado: {$validated['quantity']}");
            }

            // Reducir stock
            $item->decrement('current_stock', $validated['quantity']);

            // Registrar movimiento
            InventoryMovement::create([
                'component_id' => $item->id,
                'type' => 'out',
                'concept' => 'internet_sale',
                'quantity' => $validated['quantity'],
                'quantity_before' => $item->current_stock + $validated['quantity'],
                'quantity_after' => $item->current_stock,
                'unit_cost' => $validated['sale_price'],
                'total_cost' => $validated['quantity'] * $validated['sale_price'],
                'notes' => "Venta Internet - Plataforma: {$validated['platform']}" .
                          ($validated['order_number'] ? " - Orden: {$validated['order_number']}" : '') .
                          ($validated['customer_info'] ? " - Cliente: {$validated['customer_info']}" : '') .
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);
        });

        return back()->with('success', 'Venta por internet registrada exitosamente.');
    }

    /**
     * Registrar venta general
     */
    public function registerSale(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'sale_price' => 'required|numeric|min:0',
            'customer_info' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['quantity']) {
                throw new \Exception("Stock insuficiente. Disponible: {$item->current_stock}, Solicitado: {$validated['quantity']}");
            }

            // Reducir stock
            $item->decrement('current_stock', $validated['quantity']);

            // Registrar movimiento
            InventoryMovement::create([
                'component_id' => $item->id,
                'type' => 'out',
                'concept' => 'sale',
                'quantity' => $validated['quantity'],
                'quantity_before' => $item->current_stock + $validated['quantity'],
                'quantity_after' => $item->current_stock,
                'unit_cost' => $validated['sale_price'],
                'total_cost' => $validated['quantity'] * $validated['sale_price'],
                'notes' => "Venta" .
                          ($validated['customer_info'] ? " - Cliente: {$validated['customer_info']}" : '') .
                          ($validated['payment_method'] ? " - Pago: {$validated['payment_method']}" : '') .
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);
        });

        return back()->with('success', 'Venta registrada exitosamente.');
    }

    /**
     * Registrar venta en MercadoLibre
     */
    public function registerMercadoLibreSale(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'sale_price' => 'required|numeric|min:0',
            'ml_order_id' => 'nullable|string|max:100',
            'buyer_info' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['quantity']) {
                throw new \Exception("Stock insuficiente. Disponible: {$item->current_stock}, Solicitado: {$validated['quantity']}");
            }

            // Reducir stock
            $item->decrement('current_stock', $validated['quantity']);

            // Registrar movimiento
            InventoryMovement::create([
                'component_id' => $item->id,
                'type' => 'out',
                'concept' => 'mercadolibre_sale',
                'quantity' => $validated['quantity'],
                'quantity_before' => $item->current_stock + $validated['quantity'],
                'quantity_after' => $item->current_stock,
                'unit_cost' => $validated['sale_price'],
                'total_cost' => $validated['quantity'] * $validated['sale_price'],
                'notes' => "Venta MercadoLibre" .
                          ($validated['ml_order_id'] ? " - Orden ML: {$validated['ml_order_id']}" : '') .
                          ($validated['buyer_info'] ? " - Comprador: {$validated['buyer_info']}" : '') .
                          ($validated['shipping_method'] ? " - Envío: {$validated['shipping_method']}" : '') .
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);
        });

        return back()->with('success', 'Venta de MercadoLibre registrada exitosamente.');
    }

    /**
     * Registrar venta por página web
     */
    public function registerWebsiteSale(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'sale_price' => 'required|numeric|min:0',
            'order_number' => 'nullable|string|max:100',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'delivery_method' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['quantity']) {
                throw new \Exception("Stock insuficiente. Disponible: {$item->current_stock}, Solicitado: {$validated['quantity']}");
            }

            // Reducir stock
            $item->decrement('current_stock', $validated['quantity']);

            // Registrar movimiento
            InventoryMovement::create([
                'component_id' => $item->id,
                'type' => 'out',
                'concept' => 'website_sale',
                'quantity' => $validated['quantity'],
                'quantity_before' => $item->current_stock + $validated['quantity'],
                'quantity_after' => $item->current_stock,
                'unit_cost' => $validated['sale_price'],
                'total_cost' => $validated['quantity'] * $validated['sale_price'],
                'notes' => "Venta Página Web" .
                          ($validated['order_number'] ? " - Orden: {$validated['order_number']}" : '') .
                          ($validated['customer_email'] ? " - Email: {$validated['customer_email']}" : '') .
                          ($validated['customer_phone'] ? " - Tel: {$validated['customer_phone']}" : '') .
                          ($validated['delivery_method'] ? " - Entrega: {$validated['delivery_method']}" : '') .
                          ($validated['notes'] ? " - " . $validated['notes'] : ''),
                'created_by' => Auth::id(),
                'movement_date' => now()
            ]);
        });

        return back()->with('success', 'Venta por página web registrada exitosamente.');
    }
}