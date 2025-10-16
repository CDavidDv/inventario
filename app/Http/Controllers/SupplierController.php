<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
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
        $query = Supplier::query();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $query->orderBy($sortField, $sortDirection);

        $suppliers = $query->paginate(10)->withQueryString();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'status', 'sort', 'direction']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:supplier',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:supplier',
            'website' => 'nullable|url|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        // Cargar relaciones necesarias
        $supplier->load([
            'elementPrices.element', 
            'assignedItems',
        ]);

        // Obtener suministros recientes (movimientos con supplier_id)
        $recentSupplies = $supplier->suppliedMovements()
            ->with(['item', 'user'])
            ->where('movement_date', '>=', now()->subDays(30))
            ->orderBy('movement_date', 'desc')
            ->limit(10)
            ->get();

        // Calcular estadísticas basadas en datos existentes
        $elementPricesCount = $supplier->elementPrices()->count();
        $totalSuppliedMovements = $supplier->suppliedMovements()->count();
        $assignedItemsCount = $supplier->assignedItems()->count();
        
        // Estadísticas de precios
        $avgPurchasePrice = $supplier->suppliedMovements()
            ->whereNotNull('unit_cost')
            ->avg('unit_cost') ?? 0;
            
        $avgSellingPrice = $supplier->elementPrices()
            ->whereNotNull('selling_price')
            ->avg('selling_price') ?? 0;
            
        // Valor total de inventario basado en element_prices
        $totalInventoryValue = $supplier->suppliedMovements()
            ->join('items', 'element_prices.item_id', '=', 'items.id')
            ->whereNotNull('element_prices.purchase_price')
            ->selectRaw('SUM(items.current_stock * element_prices.purchase_price) as total')
            ->value('total') ?? 0;

        // Estadísticas de suministros recientes (últimos 30 días)
        $recentSuppliesStats = $supplier->suppliedMovements()
            ->where('movement_date', '>=', now()->subDays(30))
            ->selectRaw('
                COUNT(*) as recent_supplies_count,
                SUM(total_cost) as total_supplied_value_30d,
                COUNT(DISTINCT component_id) as supplied_items_count_30d
            ')
            ->first();

        $stats = [
            'total_elements' => $elementPricesCount,
            'total_supplied_movements' => $totalSuppliedMovements,
            'recent_supplies_count' => $recentSuppliesStats->recent_supplies_count ?? 0,
            'total_supplied_value_30d' => $recentSuppliesStats->total_supplied_value_30d ?? 0,
            'supplied_items_count_30d' => $recentSuppliesStats->supplied_items_count_30d ?? 0,
            'average_purchase_price' => $avgPurchasePrice,
            'average_selling_price' => $avgSellingPrice,
            'total_inventory_value' => $totalInventoryValue,
            'assigned_items_count' => $assignedItemsCount,
        ];

        return Inertia::render('Suppliers/Show', [
            'supplier' => $supplier,
            'stats' => $stats,
            'recentSupplies' => $recentSupplies
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('supplier')->ignore($supplier->id)],
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('supplier')->ignore($supplier->id)],
            'website' => 'nullable|url|max:255',
            'status' => 'required|in:active,inactive'
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor eliminado exitosamente.');
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(Supplier $supplier)
    {
        $supplier->toggleStatus();

        $status = $supplier->isActive() ? 'activado' : 'desactivado';

        return back()->with('success', "Proveedor {$status} exitosamente.");
    }
}