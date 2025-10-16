<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemItems;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view kits')->only(['index', 'show']);
        $this->middleware('permission:create kits')->only(['create', 'store']);
        $this->middleware('permission:edit kits')->only(['edit', 'update']);
        $this->middleware('permission:delete kits')->only(['destroy']);
        $this->middleware('permission:assemble kits')->only(['assemble']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::where('type', 'kit')->with(['kitComponents.component', 'category']);

        // Filtros
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->whereHas('kitComponents.component', function($q) {
                    $q->whereColumn('current_stock', '>=', 'items_items.quantity');
                });
            } elseif ($request->availability === 'unavailable') {
                $query->whereHas('kitComponents.component', function($q) {
                    $q->whereColumn('current_stock', '<', 'items_items.quantity');
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $kits = $query->paginate(15)->withQueryString();

        // Añadir información de disponibilidad a cada kit
        $kits->getCollection()->transform(function ($kit) {
            $kit->can_be_assembled = $this->canBeAssembled($kit);
            $kit->max_assemblies = $this->getMaxAssemblies($kit);
            $kit->total_cost = $this->getTotalCost($kit);
            return $kit;
        });

        // Obtener categorías para filtros
        $categories = Category::whereHas('items', function($q) {
            $q->where('type', 'kit');
        })->get();

        return Inertia::render('Kits/Index', [
            'kits' => $kits,
            'categories' => $categories,
            'filters' => $request->only(['category_id', 'availability', 'search', 'sort', 'direction']),
            'stats' => [
                'total' => Item::where('type', 'kit')->count(),
                'available' => Item::where('type', 'kit')->whereHas('kitComponents.component', function($q) {
                    $q->whereColumn('current_stock', '>=', 'items_items.quantity');
                })->count(),
                'categories' => Category::whereHas('items', function($q) {
                    $q->where('type', 'kit');
                })->count()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $components = Item::where('type', '!=', 'kit')
            ->where('active', true)
            ->select('id', 'name', 'current_stock', 'unit', 'category_id')
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = Category::all();

        return Inertia::render('Kits/Create', [
            'components' => $components,
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:category,id',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'components' => 'required|array|min:1',
            'components.*.component_id' => 'required|exists:items,id',
            'components.*.quantity' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            // Crear el kit
            $kit = Item::create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => 'kit',
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'min_stock' => $request->min_stock,
                'max_stock' => $request->max_stock,
                'current_stock' => 0,
                'purchase_cost' => 0, // Se calculará
                'sale_price' => $request->sale_price,
                'active' => true
            ]);

            // Calcular costo total y crear relaciones con componentes
            $totalCost = 0;
            foreach ($request->components as $componentData) {
                $component = Item::find($componentData['component_id']);
                $quantity = $componentData['quantity'];
                
                ItemItems::create([
                    'item_id' => $kit->id,
                    'item_id_2' => $component->id,
                    'quantity' => $quantity
                ]);

                $totalCost += ($component->purchase_cost ?? 0) * $quantity;
            }

            // Actualizar el costo del kit
            $kit->update(['purchase_cost' => $totalCost]);

            DB::commit();

            return redirect()->route('kits.index')
                ->with('success', 'Kit creado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el kit: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $kit)
    {
        if ($kit->type !== 'kit') {
            abort(404);
        }

        $kit->load(['kitComponents.component.category', 'category']);

        return Inertia::render('Kits/Show', [
            'kit' => $kit,
            'canBeAssembled' => $this->canBeAssembled($kit),
            'maxAssemblies' => $this->getMaxAssemblies($kit),
            'totalCost' => $this->getTotalCost($kit)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $kit)
    {
        if ($kit->type !== 'kit') {
            abort(404);
        }

        $kit->load(['kitComponents.component', 'category']);
        
        $components = Item::where('type', '!=', 'kit')
            ->where('active', true)
            ->select('id', 'name', 'current_stock', 'unit', 'category_id')
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = Category::all();

        return Inertia::render('Kits/Edit', [
            'kit' => $kit,
            'components' => $components,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $kit)
    {
        if ($kit->type !== 'kit') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:category,id',
            'unit' => 'required|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'components' => 'required|array|min:1',
            'components.*.component_id' => 'required|exists:items,id',
            'components.*.quantity' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            // Actualizar el kit
            $kit->update([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'min_stock' => $request->min_stock,
                'max_stock' => $request->max_stock,
                'sale_price' => $request->sale_price
            ]);

            // Eliminar componentes existentes
            $kit->kitComponents()->delete();

            // Crear nuevas relaciones con componentes
            $totalCost = 0;
            foreach ($request->components as $componentData) {
                $component = Item::find($componentData['component_id']);
                $quantity = $componentData['quantity'];
                
                ItemItems::create([
                    'item_id' => $kit->id,
                    'item_id_2' => $component->id,
                    'quantity' => $quantity
                ]);

                $totalCost += ($component->purchase_cost ?? 0) * $quantity;
            }

            // Actualizar el costo del kit
            $kit->update(['purchase_cost' => $totalCost]);

            DB::commit();

            return redirect()->route('kits.index')
                ->with('success', 'Kit actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el kit: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $kit)
    {
        if ($kit->type !== 'kit') {
            abort(404);
        }

        try {
            // Eliminar componentes del kit
            $kit->kitComponents()->delete();
            
            // Eliminar el kit
            $kit->delete();

            return redirect()->route('kits.index')
                ->with('success', 'Kit eliminado exitosamente');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el kit: ' . $e->getMessage()]);
        }
    }

    /**
     * Assemble a kit (create movement to reduce component stock and increase kit stock)
     */
    public function assemble(Request $request, Item $kit)
    {
        if ($kit->type !== 'kit') {
            abort(404);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        if (!$this->canBeAssembled($kit)) {
            return back()->withErrors(['error' => 'No hay suficientes componentes para ensamblar este kit']);
        }

        DB::beginTransaction();

        try {
            $quantity = $request->quantity;
            $notes = $request->notes ?? "Ensamblaje de kit {$kit->name}";

            // Reducir stock de componentes
            foreach ($kit->kitComponents as $kitComponent) {
                $component = $kitComponent->component;
                $requiredQuantity = $kitComponent->quantity * $quantity;
                
                if ($component->current_stock < $requiredQuantity) {
                    throw new \Exception("Stock insuficiente de {$component->name}");
                }

                $component->decrement('current_stock', $requiredQuantity);
            }

            // Aumentar stock del kit
            $kit->increment('current_stock', $quantity);

            DB::commit();

            return back()->with('success', "Kit {$kit->name} ensamblado exitosamente. Cantidad: {$quantity}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al ensamblar el kit: ' . $e->getMessage()]);
        }
    }

    /**
     * Check if a kit can be assembled
     */
    private function canBeAssembled(Item $kit): bool
    {
        foreach ($kit->kitComponents as $kitComponent) {
            $component = $kitComponent->component;
            if ($component->current_stock < $kitComponent->quantity) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get maximum number of kits that can be assembled
     */
    private function getMaxAssemblies(Item $kit): int
    {
        $maxAssemblies = PHP_INT_MAX;
        
        foreach ($kit->kitComponents as $kitComponent) {
            $component = $kitComponent->component;
            $possibleAssemblies = intval($component->current_stock / $kitComponent->quantity);
            $maxAssemblies = min($maxAssemblies, $possibleAssemblies);
        }
        
        return $maxAssemblies === PHP_INT_MAX ? 0 : $maxAssemblies;
    }

    /**
     * Calculate total cost of kit based on components
     */
    private function getTotalCost(Item $kit): float
    {
        $totalCost = 0;
        
        foreach ($kit->kitComponents as $kitComponent) {
            $component = $kitComponent->component;
            $totalCost += ($component->purchase_cost ?? 0) * $kitComponent->quantity;
        }
        
        return $totalCost;
    }
}
