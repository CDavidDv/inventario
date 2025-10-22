<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class InventoryMovementsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of inventory movements
     */
    public function index(Request $request)
    {
        $query = InventoryMovement::with(['component', 'user'])
            ->orderBy('movement_date', 'desc');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%")
                  ->orWhere('concept', 'LIKE', "%{$search}%")
                  ->orWhereHas('component', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('concept')) {
            $query->where('concept', $request->concept);
        }

        if ($request->filled('item_id')) {
            $query->where('component_id', $request->item_id);
        }

        if ($request->filled('user_id')) {
            $query->where('created_by', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        $movements = $query->paginate(20)->withQueryString();

        // Obtener datos para filtros
        $items = Item::select('id', 'name')->orderBy('name')->get();
        $users = User::select('id', 'name')->orderBy('name')->get();
        
        $concepts = InventoryMovement::select('concept')
            ->distinct()
            ->whereNotNull('concept')
            ->orderBy('concept')
            ->pluck('concept');

        // Estadísticas
        $stats = [
            'total_movements' => InventoryMovement::count(),
            'movements_today' => InventoryMovement::whereDate('movement_date', today())->count(),
            'movements_this_week' => InventoryMovement::whereBetween('movement_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'movements_this_month' => InventoryMovement::whereMonth('movement_date', now()->month)
                ->whereYear('movement_date', now()->year)
                ->count(),
            'total_in' => InventoryMovement::where('type', 'in')->sum('quantity'),
            'total_out' => InventoryMovement::where('type', 'out')->sum('quantity'),
        ];

        return Inertia::render('InventoryMovements/Index', [
            'movements' => $movements,
            'items' => $items,
            'users' => $users,
            'concepts' => $concepts,
            'filters' => $request->only(['search', 'type', 'concept', 'item_id', 'user_id', 'date_from', 'date_to']),
            'stats' => $stats
        ]);
    }

    /**
     * Show detailed view of a movement
     */
    public function show(InventoryMovement $movement)
    {
        $movement->load(['component', 'user', 'approver', 'relatedKit', 'relatedMovement']);

        return Inertia::render('InventoryMovements/Show', [
            'movement' => $movement
        ]);
    }

    /**
     * Export movements to CSV
     */
    public function export(Request $request)
    {
        $query = InventoryMovement::with(['component', 'user'])
            ->orderBy('movement_date', 'desc');

        // Aplicar los mismos filtros que en index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%")
                  ->orWhere('concept', 'LIKE', "%{$search}%")
                  ->orWhereHas('component', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('concept')) {
            $query->where('concept', $request->concept);
        }

        if ($request->filled('item_id')) {
            $query->where('component_id', $request->item_id);
        }

        if ($request->filled('user_id')) {
            $query->where('created_by', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        $movements = $query->get();

        $filename = 'movimientos_inventario_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($movements) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Fecha',
                'Item',
                'Tipo',
                'Concepto',
                'Cantidad',
                'Cantidad Anterior',
                'Cantidad Posterior',
                'Costo Unitario',
                'Costo Total',
                'Usuario',
                'Notas'
            ]);

            foreach ($movements as $movement) {
                fputcsv($file, [
                    $movement->id,
                    $movement->movement_date->format('Y-m-d H:i:s'),
                    $movement->component ? $movement->component->name : 'N/A',
                    $movement->getTypeLabel(),
                    $movement->concept,
                    $movement->quantity,
                    $movement->quantity_before,
                    $movement->quantity_after,
                    $movement->unit_cost,
                    $movement->total_cost,
                    $movement->user ? $movement->user->name : 'N/A',
                    $movement->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get movements summary for dashboard
     */
    public function dashboard()
    {
        // Movimientos por día (últimos 30 días)
        $dailyMovements = InventoryMovement::select(
                DB::raw('DATE(movement_date) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN type = "in" THEN quantity ELSE 0 END) as total_in'),
                DB::raw('SUM(CASE WHEN type = "out" THEN quantity ELSE 0 END) as total_out')
            )
            ->where('movement_date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top items con más movimientos
        $topItems = InventoryMovement::select('component_id', DB::raw('COUNT(*) as movement_count'))
            ->with('component:id,name')
            ->groupBy('component_id')
            ->orderBy('movement_count', 'desc')
            ->limit(10)
            ->get();

        // Movimientos por tipo
        $movementsByType = InventoryMovement::select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        // Movimientos por concepto
        $movementsByConcept = InventoryMovement::select('concept', DB::raw('COUNT(*) as count'))
            ->whereNotNull('concept')
            ->groupBy('concept')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'daily_movements' => $dailyMovements,
            'top_items' => $topItems,
            'movements_by_type' => $movementsByType,
            'movements_by_concept' => $movementsByConcept
        ]);
    }

    /**
     * Ver importaciones/exportaciones Excel agrupadas
     */
    public function excelImports(Request $request)
    {
        // Obtener movimientos de importación Excel agrupados por referencia
        $query = InventoryMovement::with(['component', 'user'])
            ->where('reference', 'LIKE', 'IMPORT-%')
            ->orderBy('movement_date', 'desc');

        // Filtros
        if ($request->filled('date_from')) {
            $query->whereDate('movement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('movement_date', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->where('created_by', $request->user_id);
        }

        // Agrupar por referencia (cada importación tiene una referencia única)
        $movements = $query->get()->groupBy('reference')->map(function ($group) {
            $first = $group->first();

            return [
                'reference' => $first->reference,
                'date' => $first->movement_date,
                'user' => $first->user,
                'total_items' => $group->count(),
                'items_created' => $group->where('concept', 'Creación de item vía importación Excel')->count(),
                'items_updated' => $group->whereIn('concept', [
                    'Incremento de stock vía importación Excel',
                    'Reducción de stock vía importación Excel',
                    'Actualización de datos vía importación Excel (sin cambio de stock)'
                ])->count(),
                'total_quantity_in' => $group->where('type', 'in')->sum('quantity'),
                'total_quantity_out' => $group->where('type', 'out')->sum('quantity'),
                'total_cost' => $group->sum('total_cost'),
                'movements' => $group->map(function ($movement) {
                    return [
                        'id' => $movement->id,
                        'type' => $movement->type,
                        'type_label' => $movement->getTypeLabel(),
                        'concept' => $movement->concept,
                        'component' => $movement->component ? [
                            'id' => $movement->component->id,
                            'name' => $movement->component->name,
                            'type' => $movement->component->type,
                        ] : null,
                        'quantity' => $movement->quantity,
                        'quantity_before' => $movement->quantity_before,
                        'quantity_after' => $movement->quantity_after,
                        'unit_cost' => $movement->unit_cost,
                        'total_cost' => $movement->total_cost,
                        'notes' => $movement->notes,
                        'metadata' => $movement->metadata,
                    ];
                })->values()
            ];
        })->values();

        // Paginar manualmente
        $perPage = 10;
        $page = $request->input('page', 1);
        $total = $movements->count();
        $items = $movements->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Estadísticas
        $stats = [
            'total_imports' => InventoryMovement::where('reference', 'LIKE', 'IMPORT-%')
                ->distinct('reference')
                ->count(DB::raw('DISTINCT reference')),
            'total_items_imported' => InventoryMovement::where('reference', 'LIKE', 'IMPORT-%')->count(),
            'imports_today' => InventoryMovement::where('reference', 'LIKE', 'IMPORT-%')
                ->whereDate('movement_date', today())
                ->distinct('reference')
                ->count(DB::raw('DISTINCT reference')),
            'imports_this_month' => InventoryMovement::where('reference', 'LIKE', 'IMPORT-%')
                ->whereMonth('movement_date', now()->month)
                ->whereYear('movement_date', now()->year)
                ->distinct('reference')
                ->count(DB::raw('DISTINCT reference')),
        ];

        // Usuarios que han realizado importaciones
        $users = User::whereIn('id', function($query) {
            $query->select('created_by')
                ->from('inventory_movements')
                ->where('reference', 'LIKE', 'IMPORT-%')
                ->distinct();
        })->get(['id', 'name', 'apellido_p']);

        return Inertia::render('InventoryMovements/ExcelImports', [
            'imports' => $paginated,
            'stats' => $stats,
            'users' => $users,
            'filters' => $request->only(['date_from', 'date_to', 'user_id'])
        ]);
    }
}
