<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage_inventory');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'components.component.category']); // Incluir todos los items (activos e inactivos)

        // Filtrar por tipo si se especifica
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filtrar por categoría si se especifica
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Buscar por nombre si se especifica
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Ordenar por stock status (bajo stock primero)
        $query->orderByRaw("
            CASE 
                WHEN current_stock < min_stock THEN 1
                WHEN current_stock = min_stock THEN 2
                WHEN current_stock <= max_stock THEN 3
                ELSE 4
            END
        ");

        $items = $query->get();

        // Procesar elementos asignados para cada item
        $items->each(function ($item) {
            if (in_array($item->type, ['kit', 'component'])) {
                $assignedElements = $item->components->map(function ($assignment) {
                    return [
                        'id' => $assignment->component->id,
                        'name' => $assignment->component->name,
                        'categoryName' => $assignment->component->category->name ?? 'Sin categoría',
                        'quantity' => $assignment->quantity,
                        'unit' => $assignment->component->unit
                    ];
                });
                
                $item->assignedElements = $assignedElements;
            } else {
                $item->assignedElements = [];
            }
        });

        // Convertir items a array para respuesta
        $itemsArray = $items->toArray();

        return response()->json($itemsArray);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:element,kit,component',
            'category_id' => 'required|exists:category,id',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:1',
            'purchase_cost' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'assignedElements' => 'nullable|array',
            'assignedElements.*.id' => 'nullable|exists:items,id',
            'assignedElements.*.quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que max_stock sea mayor que min_stock
        if ($request->max_stock <= $request->min_stock) {
            return response()->json([
                'message' => 'El stock máximo debe ser mayor que el stock mínimo'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $item = Item::create([
                'name' => $request->name,
                'type' => $request->type,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'current_stock' => $request->current_stock,
                'min_stock' => $request->min_stock,
                'max_stock' => $request->max_stock,
                'purchase_cost' => $request->purchase_cost ?? 0,
                'sale_price' => $request->sale_price ?? 0,
                'description' => $request->description,
                'location' => $request->location,
                'serial_number' => $request->serial_number,
                'active' => true,
            ]);

            // Procesar elementos asignados si existen
            if ($request->has('assignedElements') && is_array($request->assignedElements)) {
                $totalPurchaseCost = 0;
                
                foreach ($request->assignedElements as $assignedElement) {
                    if (isset($assignedElement['id']) && isset($assignedElement['quantity'])) {
                        // Verificar que el elemento existe y es de tipo 'element'
                        $element = Item::where('id', $assignedElement['id'])
                            ->where('type', 'element')
                            ->where('active', true)
                            ->first();

                        if ($element) {
                            \App\Models\ItemItems::create([
                                'item_id' => $item->id,
                                'item_id_2' => $element->id,
                                'quantity' => $assignedElement['quantity']
                            ]);
                            
                            // Calcular costo total: precio del elemento * cantidad
                            $totalPurchaseCost += ($element->purchase_cost ?? 0) * $assignedElement['quantity'];
                        }
                    }
                }
                
                // Si es kit o componente y no se especificó costo de compra, calcular automáticamente
                if (in_array($request->type, ['kit', 'component']) && !$request->has('purchase_cost')) {
                    $item->update(['purchase_cost' => $totalPurchaseCost]);
                }
            }

            DB::commit();

            // Cargar los elementos asignados para la respuesta
            $item->load(['category', 'components.component.category']);

            return response()->json([
                'message' => 'Item creado correctamente',
                'item' => $item
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        // Cargar relaciones necesarias
        $item->load(['category']);
        
        // Obtener movimientos de inventario del item
        $movements = InventoryMovement::where('component_id', $item->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Obtener elementos asignados si es kit o componente
        $assignedElements = [];
        if (in_array($item->type, ['kit', 'component'])) {
            $item->load(['components.component.category']);
            
            $assignedElements = $item->components->map(function ($assignment) {
                return [
                    'id' => $assignment->component->id,
                    'name' => $assignment->component->name,
                    'categoryName' => $assignment->component->category->name ?? 'Sin categoría',
                    'quantity' => $assignment->quantity,
                    'unit' => $assignment->component->unit,
                    'type' => $assignment->component->type,
                    'current_stock' => $assignment->component->current_stock
                ];
            });
        }
        
        // Encontrar kits/componentes que usan este item
        $usedInItems = [];
        if ($item->type === 'element') {
            $usedIn = Item::whereHas('components', function ($query) use ($item) {
                $query->where('id', $item->id);
            })->with(['category'])->get();
            
            $usedInItems = $usedIn->map(function ($parentItem) use ($item) {
                $assignment = $parentItem->components->where('id', $item->id)->first();
                return [
                    'id' => $parentItem->id,
                    'name' => $parentItem->name,
                    'type' => $parentItem->type,
                    'categoryName' => $parentItem->category->name ?? 'Sin categoría',
                    'quantity' => $assignment ? $assignment->quantity : 0
                ];
            });
        }
        
        // Estadísticas del item
        $stats = [
            'total_movements' => InventoryMovement::where('component_id', $item->id)->count(),
            'total_entries' => InventoryMovement::where('component_id', $item->id)
                ->where('type', 'in')->sum('quantity'),
            'total_exits' => InventoryMovement::where('component_id', $item->id)
                ->where('type', 'out')->sum('quantity'),
            'total_adjustments' => InventoryMovement::where('component_id', $item->id)
                ->where('type', 'adjustment')->count(),
        ];
        
        return inertia('Items/Show', [
            'item' => $item,
            'movements' => $movements,
            'assignedElements' => $assignedElements,
            'usedInItems' => $usedInItems,
            'stats' => $stats
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        Log::info('Item update attempt', [
            'item_id' => $item->id,
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:category,id',
            'unit' => 'sometimes|required|string|max:50',
            'current_stock' => 'sometimes|required|numeric|min:0',
            'min_stock' => 'sometimes|required|numeric|min:0',
            'max_stock' => 'sometimes|required|numeric|min:1',
            'purchase_cost' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'assignedTo' => 'nullable|exists:items,id',
            'active' => 'sometimes|boolean',
            'assignedElements' => 'nullable|array',
            'assignedElements.*.id' => 'nullable|exists:items,id',
            'assignedElements.*.quantity' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            Log::warning('Item validation failed', [
                'item_id' => $item->id,
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que max_stock sea mayor que min_stock si se están actualizando
        if ($request->has('max_stock') && $request->has('min_stock')) {
            if ($request->max_stock <= $request->min_stock) {
                Log::warning('Item stock validation failed', [
                    'item_id' => $item->id,
                    'min_stock' => $request->min_stock,
                    'max_stock' => $request->max_stock
                ]);
                
                return response()->json([
                    'message' => 'El stock máximo debe ser mayor que el stock mínimo'
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Filtrar solo los campos que existen en la tabla items
            $fieldsToUpdate = $request->only([
                'name', 'category_id', 'unit', 'current_stock', 'min_stock', 'max_stock',
                'purchase_cost', 'sale_price', 'description', 'location', 'serial_number'
            ]);

            Log::info('Updating item fields', [
                'item_id' => $item->id,
                'fields_to_update' => $fieldsToUpdate
            ]);

            $item->update($fieldsToUpdate);

            // Procesar elementos asignados si existen
            if ($request->has('assignedElements') && is_array($request->assignedElements)) {
                // Eliminar asignaciones existentes
                \App\Models\ItemItems::where('item_id', $item->id)->delete();
                
                $totalPurchaseCost = 0;
                
                // Crear nuevas asignaciones
                foreach ($request->assignedElements as $assignedElement) {
                    if (isset($assignedElement['id']) && isset($assignedElement['quantity'])) {
                        // Verificar que el elemento existe y es de tipo 'element'
                        $element = Item::where('id', $assignedElement['id'])
                            ->where('type', 'element')
                            ->where('active', true)
                            ->first();

                        if ($element) {
                            \App\Models\ItemItems::create([
                                'item_id' => $item->id,
                                'item_id_2' => $element->id,
                                'quantity' => $assignedElement['quantity']
                            ]);
                            
                            // Calcular costo total: precio del elemento * cantidad
                            $totalPurchaseCost += ($element->purchase_cost ?? 0) * $assignedElement['quantity'];
                        }
                    }
                }
                
                // Si es kit o componente y no se especificó costo de compra, calcular automáticamente
                if (in_array($item->type, ['kit', 'component']) && !$request->has('purchase_cost')) {
                    $item->update(['purchase_cost' => $totalPurchaseCost]);
                }
            }

            // Si se está asignando a otro item, manejarlo por separado
            // ya que assignedTo no es un campo directo de la tabla items
            if ($request->has('assignedTo')) {
                // Aquí podrías implementar la lógica de asignación
                // Por ejemplo, crear un registro en una tabla pivot
                Log::info('Assignment requested', [
                    'item_id' => $item->id,
                    'assignedTo' => $request->assignedTo
                ]);
            }

            DB::commit();

            Log::info('Item updated successfully', [
                'item_id' => $item->id,
                'updated_item' => $item->toArray()
            ]);

            // Cargar los elementos asignados para la respuesta
            $item->load(['category', 'components.component.category']);

            return response()->json([
                'message' => 'Item actualizado correctamente',
                'item' => $item
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating item: ' . $e->getMessage(), [
                'item_id' => $item->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error al actualizar el item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        try {
            // En lugar de eliminar físicamente, marcamos como inactivo
            $item->update(['active' => false]);

            return response()->json([
                'message' => 'Item eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items by type
     */
    public function getByType($type)
    {
        $validator = Validator::make(['type' => $type], [
            'type' => 'required|in:element,kit,component'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Tipo de item inválido'
            ], 422);
        }

        $items = Item::with(['category'])
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($items);
    }

    /**
     * Get items by category
     */
    public function getByCategory($categoryId)
    {
        $items = Item::with(['category'])
            ->where('category_id', $categoryId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($items);
    }

    /**
     * Search items
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type');
        $categoryId = $request->get('category_id');

        $items = Item::with(['category']);

        if ($query) {
            $items->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('serial_number', 'like', "%{$query}%");
            });
        }

        if ($type) {
            $items->where('type', $type);
        }

        if ($categoryId) {
            $items->where('category_id', $categoryId);
        }

        $items = $items->orderBy('created_at', 'desc')->get();

        return response()->json($items);
    }

    /**
     * Adjust stock
     */
    public function adjustStock(Request $request, Item $item)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric',
            'type' => 'required|in:add,remove,set',
            'reason' => 'required|string|max:255',
            'concept' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

        $oldStock = $item->current_stock;
            
            switch ($request->type) {
                case 'add':
                    $item->current_stock += $request->quantity;
                    break;
                case 'remove':
                    if ($item->current_stock < $request->quantity) {
                        return response()->json([
                            'message' => 'Stock insuficiente para remover'
                        ], 422);
                    }
                    $item->current_stock -= $request->quantity;
                    break;
                case 'set':
                    $item->current_stock = $request->quantity;
                    break;
            }

            $item->save();

            // Aquí podrías registrar el movimiento en la tabla de movimientos
            // InventoryMovement::create([...]);

            DB::commit();

            return response()->json([
                'message' => 'Stock ajustado correctamente',
                'old_stock' => $oldStock,
                'new_stock' => $item->current_stock,
                'item' => $item->load('category')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al ajustar el stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar un componente/elemento a un kit
     */
    public function assignComponent(Request $request, Item $kit)
    {
        // Validar que el kit sea realmente un kit
        if ($kit->type !== 'kit') {
            return response()->json([
                'message' => 'El item debe ser de tipo kit para asignar componentes'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'component_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $component = Item::findOrFail($request->component_id);
            
            // Validar que el componente sea elemento o componente
            if (!in_array($component->type, ['element', 'component'])) {
                return response()->json([
                    'message' => 'Solo se pueden asignar elementos y componentes a kits'
                ], 422);
            }

            // Verificar si ya existe la asignación
            $existingAssignment = \App\Models\ItemItems::where('item_id', $kit->id)
                ->where('item_id_2', $component->id)
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'message' => 'Este componente ya está asignado al kit'
                ], 422);
            }

            // Crear la asignación
            $assignment = \App\Models\ItemItems::create([
                'item_id' => $kit->id,
                'item_id_2' => $component->id,
                'quantity' => $request->quantity
            ]);

            DB::commit();

            Log::info('Component assigned to kit', [
                'kit_id' => $kit->id,
                'kit_name' => $kit->name,
                'component_id' => $component->id,
                'component_name' => $component->name,
                'quantity' => $request->quantity,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Componente asignado correctamente',
                'assignment' => $assignment->load(['component', 'kit'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error assigning component to kit', [
                'kit_id' => $kit->id,
                'component_id' => $request->component_id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'message' => 'Error al asignar el componente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desasignar un componente de un kit
     */
    public function unassignComponent(Item $kit, Item $component)
    {
        // Validar que el kit sea realmente un kit
        if ($kit->type !== 'kit') {
            return response()->json([
                'message' => 'El item debe ser de tipo kit'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Buscar y eliminar la asignación
            $assignment = \App\Models\ItemItems::where('item_id', $kit->id)
                ->where('item_id_2', $component->id)
                ->first();

            if (!$assignment) {
                return response()->json([
                    'message' => 'No se encontró la asignación'
                ], 404);
            }

            $assignment->delete();

            DB::commit();

            Log::info('Component unassigned from kit', [
                'kit_id' => $kit->id,
                'kit_name' => $kit->name,
                'component_id' => $component->id,
                'component_name' => $component->name,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Componente desasignado correctamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error unassigning component from kit', [
                'kit_id' => $kit->id,
                'component_id' => $component->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'message' => 'Error al desasignar el componente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener los componentes/elementos asignados a un kit o componente
     */
    public function getComponents(Item $kit)
    {
        // Validar que el item sea kit o componente
        if (!in_array($kit->type, ['kit', 'component'])) {
            return response()->json([
                'message' => 'El item debe ser de tipo kit o component'
            ], 422);
        }

        try {
            $components = $kit->components()
                ->with(['component.category'])
                ->get()
                ->map(function ($assignment) {
                    return [
                        'id' => $assignment->component->id,
                        'name' => $assignment->component->name,
                        'categoryName' => $assignment->component->category->name ?? 'Sin categoría',
                        'type' => $assignment->component->type,
                        'quantity' => $assignment->quantity,
                        'current_stock' => $assignment->component->current_stock,
                        'min_stock' => $assignment->component->min_stock,
                        'max_stock' => $assignment->component->max_stock,
                        'unit' => $assignment->component->unit,
                        'active' => $assignment->component->active,
                        'stock_status' => $assignment->component->stock_status,
                        'stock_status_text' => $assignment->component->stock_status_text,
                        'stock_status_color' => $assignment->component->stock_status_color
                    ];
                });

            return response()->json($components);

        } catch (\Exception $e) {
            Log::error('Error getting kit components', [
                'kit_id' => $kit->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'message' => 'Error al obtener los componentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar el estado activo/inactivo de un item
     */
    public function toggleActive(Item $item)
    {
        try {
            DB::beginTransaction();

            // Cambiar el estado
            $item->active = !$item->active;
            $item->save();

            DB::commit();

            Log::info('Item active status toggled', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'new_status' => $item->active ? 'active' : 'inactive',
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => $item->active ? 'Item activado correctamente' : 'Item desactivado correctamente',
                'item' => $item->load('category')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error toggling item active status', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'message' => 'Error al cambiar el estado del item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener elementos disponibles para asignación
     */
    public function getAvailableElements(Request $request)
    {
        try {
            $type = $request->get('type', 'element'); // Por defecto solo elementos
            
            $query = Item::with('category')
                ->where('active', true);
            
            // Filtrar por tipo según el tipo de item que se está creando/editando
            if ($type === 'kit') {
                // Para kits: elementos y componentes
                $query->whereIn('type', ['element', 'component']);
            } else if ($type === 'component') {
                // Para componentes: solo elementos
                $query->where('type', 'element');
            } else {
                // Para elementos: nada
                $query->where('type', 'element');
            }

            // Filtrar por búsqueda si se proporciona
            if ($request->has('search') && !empty($request->search)) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Filtrar por categoría si se proporciona
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            $elements = $query->orderBy('name')->get();

            return response()->json($elements);

        } catch (\Exception $e) {
            Log::error('Error getting available elements', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'message' => 'Error al obtener los elementos disponibles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
