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
}
