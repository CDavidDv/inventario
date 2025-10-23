<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\InventoryAlert;
use App\Models\InventoryMovement;
use App\Services\InventoryAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventoryDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_dashboard');
    }

    public function index()
    {
        // Estadísticas generales
        $stats = [
            'total_items' => Item::count(),
            'total_elements' => Item::where('type', 'element')->count(),
            'total_kits' => Item::where('type', 'kit')->count(),
            'total_components' => Item::where('type', 'component')->count(),
            'low_stock_elements' => Item::where('type', 'element')->where('current_stock', '<=', 'min_stock')->count(),
            'over_stock_elements' => Item::where('type', 'element')->where('current_stock', '>=', 'max_stock')->count(),
            'low_stock_kits' => Item::where('type', 'kit')->where('current_stock', '<=', 'min_stock')->count(),
            'over_stock_kits' => Item::where('type', 'kit')->where('current_stock', '>=', 'max_stock')->count(),
            'low_stock_components' => Item::where('type', 'component')->where('current_stock', '<=', 'min_stock')->count(),
            'over_stock_components' => Item::where('type', 'component')->where('current_stock', '>=', 'max_stock')->count(),
            'total_categories' => Category::count(),
            'total_value' => Item::sum(DB::raw('current_stock * purchase_cost'))
        ];

        // Categorías por tipo
        $elementCategories = Category::where('type', 'element')->where('active', true)->get();
        $kitCategories = Category::where('type', 'kit')->where('active', true)->get();
        $componentCategories = Category::where('type', 'component')->where('active', true)->get();

        $elements = Item::where('type', 'element')->where('active', true)->with(['category'])->get();
        $kits = Item::where('type', 'kit')->where('active', true)->with(['category'])->get();
        $components = Item::where('type', 'component')->where('active', true)->with(['category'])->get();

        $avalibleItems = Item::where('active', true)->with(['category'])->get();
        
        // Items por tipo con sus categorías y relaciones
        $elementItems = Item::with(['category'])
            ->where('type', 'element')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'type' => $item->type,
                    'category_id' => $item->category_id,
                    'categoryName' => $item->category ? $item->category->name : 'Sin categoría',
                    'unit' => $item->unit,
                    'min_stock' => $item->min_stock,
                    'max_stock' => $item->max_stock,
                    'current_stock' => $item->current_stock,
                    'purchase_cost' => $item->purchase_cost,
                    'sale_price' => $item->sale_price,
                    'location' => $item->location,
                    'serial_number' => $item->serial_number,
                    'active' => $item->active,
                    'assignedTo' => $item->assignedTo,
                    'assignedElements' => [] // Los elementos no tienen elementos asignados
                ];
            });

        $kitItems = Item::with(['category', 'components.component.category'])
            ->where('type', 'kit')
            ->get()
            ->map(function ($item) {
                // Obtener elementos asignados al kit
                $assignedElements = $item->components->map(function ($assignment) {
                    return [
                        'id' => $assignment->component->id,
                        'name' => $assignment->component->name,
                        'categoryName' => $assignment->component->category->name ?? 'Sin categoría',
                        'quantity' => $assignment->quantity,
                        'unit' => $assignment->component->unit,
                        'type' => $assignment->component->type
                    ];
                })->toArray();

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'type' => $item->type,
                    'category_id' => $item->category_id,
                    'categoryName' => $item->category ? $item->category->name : 'Sin categoría',
                    'unit' => $item->unit,
                    'min_stock' => $item->min_stock,
                    'max_stock' => $item->max_stock,
                    'current_stock' => $item->current_stock,
                    'purchase_cost' => $item->purchase_cost,
                    'sale_price' => $item->sale_price,
                    'location' => $item->location,
                    'serial_number' => $item->serial_number,
                    'active' => $item->active,
                    'assignedTo' => $item->assignedTo,
                    'assignedElements' => $assignedElements
                ];
            });

        $componentItems = Item::with(['category', 'components.component.category'])
            ->where('type', 'component')
            ->get()
            ->map(function ($item) {
                // Obtener elementos asignados al componente
                $assignedElements = $item->components->map(function ($assignment) {
                    return [
                        'id' => $assignment->component->id,
                        'name' => $assignment->component->name,
                        'categoryName' => $assignment->component->category->name ?? 'Sin categoría',
                        'quantity' => $assignment->quantity,
                        'unit' => $assignment->component->unit,
                        'type' => $assignment->component->type
                    ];
                })->toArray();

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'type' => $item->type,
                    'category_id' => $item->category_id,
                    'categoryName' => $item->category ? $item->category->name : 'Sin categoría',
                    'unit' => $item->unit,
                    'min_stock' => $item->min_stock,
                    'max_stock' => $item->max_stock,
                    'current_stock' => $item->current_stock,
                    'purchase_cost' => $item->purchase_cost,
                    'sale_price' => $item->sale_price,
                    'location' => $item->location,
                    'serial_number' => $item->serial_number,
                    'active' => $item->active,
                    'assignedTo' => $item->assignedTo,
                    'assignedElements' => $assignedElements
                ];
            });

        // Items con stock bajo
        $lowStockItems = Item::with(['category'])
            ->where('current_stock', '<=', 'min_stock')
            ->orderBy('current_stock', 'asc')
            ->limit(10)
            ->get();

        // Items con sobre stock
        $overStockItems = Item::with(['category'])
            ->where('current_stock', '>=', 'max_stock')
            ->orderBy('current_stock', 'desc')
            ->limit(10)
            ->get();

        // Distribución por categorías
        $categoryDistribution = Category::withCount('items')
            ->orderBy('items_count', 'desc')
            ->get();

        // Top 10 items por valor
        $topValueItems = Item::select('name', 'current_stock', 'purchase_cost', DB::raw('current_stock * purchase_cost as total_value'))
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->get();

        // Gráfico de stock por mes (últimos 6 meses)
        $monthlyStock = $this->getMonthlyStockData();

        return Inertia::render('Inventario/index', [
            'stats' => $stats,
            'lowStockItems' => $lowStockItems,
            'overStockItems' => $overStockItems,
            'categoryDistribution' => $categoryDistribution,
            'topValueItems' => $topValueItems,
            'monthlyStock' => $monthlyStock,
            'elementCategories' => $elementCategories,
            'kitCategories' => $kitCategories,
            'componentCategories' => $componentCategories,
            'elementItems' => $elementItems,
            'kitItems' => $kitItems,
            'componentItems' => $componentItems,
            'elements' => $elements,
            'kits' => $kits,
            'components' => $components,
            'avalibleItems' => $avalibleItems
        ]);
    }

    private function getMonthlyStockData(): array
    {
        $months = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // Aquí podrías agregar lógica para obtener datos históricos de stock
            // Por ahora retornamos datos de ejemplo
            $data[] = rand(100, 1000);
        }

        return [
            'labels' => $months,
            'data' => $data
        ];
    }

    public function markAlertAsRead(Request $request, int $alertId)
    {
        $this->middleware('permission:manage_inventory');
        
        
        
        return back()->with('error', 'Error al marcar la alerta');
    }

    public function getStockChart()
    {
        // Datos para gráficos de stock
        $stockData = Item::select('name', 'current_stock', 'min_stock', 'max_stock')
            ->orderBy('current_stock', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'current' => $item->current_stock,
                    'min' => $item->min_stock,
                    'max' => $item->max_stock,
                    'percentage' => $item->getStockPercentage()
                ];
            });

        return response()->json($stockData);
    }
}
