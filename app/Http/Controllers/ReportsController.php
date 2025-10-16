<?php

namespace App\Http\Controllers;

use App\Models\Element;
use App\Models\Supplier;
use App\Models\Kit;
use App\Models\StockAlert;
use App\Models\ActivityLog;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view reports');
    }

    /**
     * Dashboard principal de reportes
     */
    public function index()
    {
        return Inertia::render('Reports/Index', [
            'stats' => $this->getGeneralStats()
        ]);
    }

    /**
     * Reporte de estado del stock
     */
    public function stockStatus(Request $request)
    {
        $query = Element::with(['stockAlerts' => function($q) {
            $q->where('is_resolved', false);
        }]);

        // Filtros
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->where('current_stock', '<=', 0);
                    break;
                case 'over':
                    $query->whereColumn('current_stock', '>=', 'max_stock');
                    break;
            }
        }

        $elements = $query->get();

        $stockData = [
            'normal' => $elements->filter(function($element) {
                return $element->current_stock > $element->min_stock && 
                       ($element->max_stock == 0 || $element->current_stock < $element->max_stock);
            })->count(),
            'low' => $elements->filter(function($element) {
                return $element->current_stock <= $element->min_stock && $element->current_stock > 0;
            })->count(),
            'out' => $elements->filter(function($element) {
                return $element->current_stock <= 0;
            })->count(),
            'over' => $elements->filter(function($element) {
                return $element->max_stock > 0 && $element->current_stock >= $element->max_stock;
            })->count()
        ];

        return Inertia::render('Reports/StockStatus', [
            'elements' => $elements,
            'stockData' => $stockData,
            'categories' => Element::distinct()->pluck('category')->filter(),
            'filters' => $request->only(['category', 'status'])
        ]);
    }

    /**
     * Exportar elementos a CSV
     */
    public function exportElements()
    {
        $elements = Element::with(['prices.supplier', 'stockAlerts'])->get();

        $filename = 'elementos_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($elements) {
            $file = fopen('php://output', 'w');
            
            // Encabezados CSV
            fputcsv($file, [
                'ID',
                'Nombre',
                'Descripción',
                'Categoría',
                'Unidad',
                'Stock Actual',
                'Stock Mínimo',
                'Stock Máximo',
                'Ubicación',
                'Estado',
                'Precio Compra Promedio',
                'Precio Venta Promedio',
                'Valor Inventario',
                'Alertas Activas',
                'Fecha Creación'
            ]);

            foreach ($elements as $element) {
                $avgPurchase = $element->prices->avg('purchase_price') ?: 0;
                $avgSelling = $element->prices->avg('selling_price') ?: 0;
                $inventoryValue = $element->current_stock * $avgPurchase;
                
                $status = 'Normal';
                if ($element->current_stock <= 0) $status = 'Agotado';
                elseif ($element->current_stock <= $element->min_stock) $status = 'Stock Bajo';
                elseif ($element->max_stock > 0 && $element->current_stock >= $element->max_stock) $status = 'Sobre Stock';

                fputcsv($file, [
                    $element->id,
                    $element->name,
                    $element->description,
                    $element->category,
                    $element->unit,
                    $element->current_stock,
                    $element->min_stock,
                    $element->max_stock,
                    $element->location,
                    $status,
                    number_format($avgPurchase, 2),
                    number_format($avgSelling, 2),
                    number_format($inventoryValue, 2),
                    $element->stockAlerts->where('is_resolved', false)->count(),
                    $element->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Dashboard analítico principal
     */
    public function analyticsDashboard()
    {
        $kpis = $this->calculateKpis();
        
        return Inertia::render('Analytics/Dashboard', [
            'initialKpis' => $kpis
        ]);
    }

    /**
     * API: Obtener KPIs del dashboard
     */
    public function getDashboardKpis()
    {
        return response()->json($this->calculateKpis());
    }

    /**
     * API: Obtener datos para gráfico de movimientos de stock
     */
    public function getStockMovementsChart(Request $request)
    {
        $period = $request->get('period', 30);
        $elementId = $request->get('element_id');
        
        $startDate = now()->subDays($period);
        
        $query = InventoryMovement::where('movement_date', '>=', $startDate);
        
        if ($elementId) {
            $query->where('element_id', $elementId);
        }
        
        $movements = $query->orderBy('movement_date')
            ->selectRaw('DATE(movement_date) as date, SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as incoming, SUM(CASE WHEN quantity < 0 THEN quantity ELSE 0 END) as outgoing')
            ->groupBy('date')
            ->get();
        
        // Calcular stock acumulado
        $cumulative = 0;
        $movements = $movements->map(function($item) use (&$cumulative) {
            $cumulative += $item->incoming + $item->outgoing;
            $item->cumulative = $cumulative;
            return $item;
        });
        
        return response()->json([
            'movements' => $movements
        ]);
    }

    /**
     * API: Obtener estadísticas por categorías
     */
    public function getCategoryStats(Request $request)
    {
        $metric = $request->get('metric', 'count');
        
        $categories = Element::with(['prices'])
            ->selectRaw('category, COUNT(*) as count, SUM(current_stock) as total_stock')
            ->groupBy('category')
            ->get()
            ->map(function($category) {
                $avgPrice = Element::where('category', $category->category)
                    ->with('prices')
                    ->get()
                    ->map(function($element) {
                        return $element->prices->avg('purchase_price') ?: 0;
                    })->avg();
                
                $category->total_value = $category->total_stock * $avgPrice;
                return $category;
            });
        
        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * API: Obtener tendencia de alertas
     */
    public function getAlertsTrend(Request $request)
    {
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);
        
        $alerts = StockAlert::where('date', '>=', $startDate)
            ->selectRaw('DATE(date) as date, alert_type, COUNT(*) as count')
            ->groupBy('date', 'alert_type')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function($dayAlerts, $date) {
                $result = ['date' => $date];
                foreach($dayAlerts as $alert) {
                    $result[strtolower($alert->alert_type)] = $alert->count;
                }
                return $result;
            })
            ->values();
        
        return response()->json([
            'alerts' => $alerts
        ]);
    }

    /**
     * API: Resumen del análisis ABC
     */
    public function getAbcSummary()
    {
        $elements = Element::with(['prices'])
            ->where('current_stock', '>', 0)
            ->get()
            ->map(function($element) {
                $avgPrice = $element->prices->avg('purchase_price') ?: 0;
                $element->inventory_value = $element->current_stock * $avgPrice;
                return $element;
            })
            ->sortByDesc('inventory_value');

        $totalValue = $elements->sum('inventory_value');
        $cumulativeValue = 0;

        $elements = $elements->map(function($element) use ($totalValue, &$cumulativeValue) {
            $cumulativeValue += $element->inventory_value;
            $element->cumulative_percentage = ($cumulativeValue / $totalValue) * 100;
            
            if ($element->cumulative_percentage <= 70) {
                $element->abc_category = 'A';
            } elseif ($element->cumulative_percentage <= 90) {
                $element->abc_category = 'B';
            } else {
                $element->abc_category = 'C';
            }
            
            return $element;
        });

        $categoryA = $elements->where('abc_category', 'A');
        $categoryB = $elements->where('abc_category', 'B');
        $categoryC = $elements->where('abc_category', 'C');

        return response()->json([
            'categoryA' => 70,
            'categoryB' => 20,
            'categoryC' => 10,
            'itemsA' => $categoryA->count(),
            'itemsB' => $categoryB->count(),
            'itemsC' => $categoryC->count()
        ]);
    }

    /**
     * API: Top elementos por valor
     */
    public function getTopValueItems(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $elements = Element::with(['prices'])
            ->get()
            ->map(function($element) {
                $avgPrice = $element->prices->avg('purchase_price') ?: 0;
                $element->total_value = $element->current_stock * $avgPrice;
                return $element;
            })
            ->sortByDesc('total_value')
            ->take($limit)
            ->values();
        
        return response()->json([
            'items' => $elements
        ]);
    }

    /**
     * API: Top elementos por rotación
     */
    public function getTopTurnoverItems(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 30);
        
        $startDate = now()->subDays($period);
        
        $elements = Element::withCount(['inventoryMovements as movements_count' => function($query) use ($startDate) {
                $query->where('movement_date', '>=', $startDate);
            }])
            ->with(['inventoryMovements' => function($query) use ($startDate) {
                $query->where('movement_date', '>=', $startDate);
            }])
            ->having('movements_count', '>', 0)
            ->orderByDesc('movements_count')
            ->take($limit)
            ->get()
            ->map(function($element) {
                $element->total_moved = $element->inventoryMovements->sum('quantity');
                return $element;
            });
        
        return response()->json([
            'items' => $elements
        ]);
    }

    /**
     * Calcular KPIs principales
     */
    private function calculateKpis()
    {
        $totalElements = Element::count();
        $criticalItems = Element::lowStock()->count() + Element::where('current_stock', '<=', 0)->count();
        
        // Valor total del inventario
        $totalInventoryValue = Element::join('element_prices', 'elements.id', '=', 'element_prices.element_id')
            ->sum(DB::raw('elements.current_stock * element_prices.purchase_price'));
        
        // Rotación de inventario (simplificado)
        $avgStock = Element::avg('current_stock');
        $monthlyMovements = InventoryMovement::where('movement_date', '>=', now()->subDays(30))
            ->sum(DB::raw('ABS(quantity)'));
        $inventoryTurnover = $avgStock > 0 ? ($monthlyMovements / $avgStock) : 0;
        
        // Eficiencia de stock
        $normalStock = $totalElements - $criticalItems;
        $stockEfficiency = $totalElements > 0 ? ($normalStock / $totalElements) * 100 : 0;
        
        return [
            'totalInventoryValue' => $totalInventoryValue,
            'inventoryGrowth' => rand(-5, 15), // Simulado
            'inventoryTurnover' => $inventoryTurnover,
            'avgDaysInStock' => $inventoryTurnover > 0 ? (30 / $inventoryTurnover) : 0,
            'criticalItems' => $criticalItems,
            'totalElements' => $totalElements,
            'stockEfficiency' => $stockEfficiency
        ];
    }

    /**
     * Obtener estadísticas generales del sistema
     */
    private function getGeneralStats()
    {
        return [
            'elements' => [
                'total' => Element::count(),
                'low_stock' => Element::lowStock()->count(),
                'out_of_stock' => Element::where('current_stock', '<=', 0)->count(),
                'categories' => Element::distinct('category')->count()
            ],
            'suppliers' => [
                'total' => Supplier::count(),
                'active' => Supplier::whereHas('elementPrices')->count(),
                'countries' => Supplier::distinct('country')->count()
            ],
            'kits' => [
                'total' => Kit::count(),
                'available' => Kit::whereHas('kitComponents.element', function($q) {
                    $q->whereColumn('current_stock', '>=', 'kit_components.quantity');
                })->count()
            ],
            'alerts' => [
                'total' => StockAlert::where('is_resolved', false)->count(),
                'critical' => StockAlert::where('is_resolved', false)
                    ->whereIn('alert_type', ['Agotado', 'Crítico'])->count(),
                'recent' => StockAlert::where('is_resolved', false)
                    ->where('date', '>=', now()->subDays(7))->count()
            ]
        ];
    }
}