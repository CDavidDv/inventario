<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\InventoryAlert;
use App\Models\InventoryMovement;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function dashboard()
    {
        $dashboardData = $this->getDashboardData();

        return Inertia::render('Dashboard/index', array_merge([
            'message' => 'Dashboard principal - Sistema de Inventario',
        ], $dashboardData));
    }

    private function getDashboardData(): array
    {
        $limits = config('dashboard.limits', [
            'low_stock_components' => 5,
            'unread_alerts' => 5,
            'recent_movements' => 10,
            'category_distribution' => 6,
            'top_value_components' => 5,
        ]);

        // Obtener todos los items activos con sus categorías
        $items = Item::with(['category'])->where('active', true)->get();
        
        // Separar por tipos
        $elementItems = $items->where('type', 'element')->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'current_stock' => $item->current_stock,
                'min_stock' => $item->min_stock,
                'max_stock' => $item->max_stock,
                'purchase_cost' => $item->purchase_cost ?? 0,
                'categoryName' => $item->category->name ?? 'Sin categoría',
                'type' => $item->type
            ];
        })->values();

        $kitItems = $items->where('type', 'kit')->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'current_stock' => $item->current_stock,
                'min_stock' => $item->min_stock,
                'max_stock' => $item->max_stock,
                'purchase_cost' => $item->purchase_cost ?? 0,
                'categoryName' => $item->category->name ?? 'Sin categoría',
                'type' => $item->type
            ];
        })->values();

        $componentItems = $items->where('type', 'component')->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'current_stock' => $item->current_stock,
                'min_stock' => $item->min_stock,
                'max_stock' => $item->max_stock,
                'purchase_cost' => $item->purchase_cost ?? 0,
                'categoryName' => $item->category->name ?? 'Sin categoría',
                'type' => $item->type
            ];
        })->values();

        // Estadísticas generales del inventario
        $stats = [
            'total_items' => $items->count(),
            'total_elements' => $elementItems->count(),
            'total_kits' => $kitItems->count(),
            'total_components' => $componentItems->count(),
            'low_stock_elements' => $elementItems->filter(function($item) { return $item['current_stock'] <= $item['min_stock']; })->count(),
            'normal_stock_elements' => $elementItems->filter(function($item) { return $item['current_stock'] > $item['min_stock'] && $item['current_stock'] < $item['max_stock']; })->count(),
            'over_stock_elements' => $elementItems->filter(function($item) { return $item['current_stock'] >= $item['max_stock']; })->count(),
            'low_stock_kits' => $kitItems->filter(function($item) { return $item['current_stock'] <= $item['min_stock']; })->count(),
            'normal_stock_kits' => $kitItems->filter(function($item) { return $item['current_stock'] > $item['min_stock'] && $item['current_stock'] < $item['max_stock']; })->count(),
            'over_stock_kits' => $kitItems->filter(function($item) { return $item['current_stock'] >= $item['max_stock']; })->count(),
            'low_stock_components' => $componentItems->filter(function($item) { return $item['current_stock'] <= $item['min_stock']; })->count(),
            'normal_stock_components' => $componentItems->filter(function($item) { return $item['current_stock'] > $item['min_stock'] && $item['current_stock'] < $item['max_stock']; })->count(),
            'over_stock_components' => $componentItems->filter(function($item) { return $item['current_stock'] >= $item['max_stock']; })->count(),
            'total_categories' => Category::count(),
            'total_value' => $items->sum(function($item) { return $item->current_stock * ($item->purchase_cost ?? 0); })
        ];

        // Items con stock bajo (todos los tipos)
        $lowStockItems = $items->filter(function ($item) {
            return $item->current_stock <= $item->min_stock;
        })->sortBy('current_stock')->take($limits['low_stock_components'])->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'current_stock' => $item->current_stock,
                'min_stock' => $item->min_stock,
                'max_stock' => $item->max_stock,
                'categoryName' => $item->category->name ?? 'Sin categoría',
                'type' => $item->type
            ];
        })->values();

        // Items con sobre stock
        $overStockItems = $items->filter(function ($item) {
            return $item->current_stock >= $item->max_stock;
        })->sortByDesc('current_stock')->take($limits['low_stock_components'])->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'current_stock' => $item->current_stock,
                'min_stock' => $item->min_stock,
                'max_stock' => $item->max_stock,
                'categoryName' => $item->category->name ?? 'Sin categoría',
                'type' => $item->type
            ];
        })->values();

        // Movimientos recientes
        $recentMovements = [];
        if (class_exists('App\Models\InventoryMovement')) {
            $recentMovements = InventoryMovement::with(['item', 'component', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit($limits['recent_movements'])
                ->get()
                ->map(function ($movement) {
                    return [
                        'id' => $movement->id,
                        'type' => $movement->getTypeLabel(), // Usar etiqueta amigable
                        'quantity' => $movement->quantity,
                        'created_at' => $movement->created_at,
                        'component' => [
                            'name' => $movement->item->name ?? $movement->component->name ?? 'Item desconocido',
                            'id' => $movement->item->id ?? $movement->component->id ?? null
                        ],
                        'user' => [
                            'name' => $movement->user->name ?? 'Usuario desconocido',
                            'id' => $movement->user->id ?? null
                        ]
                    ];
                })->values();
        }

        // Distribución por categorías
        $categoryDistribution = Category::withCount('items')
            ->orderBy('items_count', 'desc')
            ->limit($limits['category_distribution'])
            ->get();

        // Top items por valor
        $topValueComponents = Item::select('name', 'current_stock', 'purchase_cost', DB::raw('current_stock * purchase_cost as total_value'))
            ->where('purchase_cost', '>', 0)
            ->orderBy('total_value', 'desc')
            ->limit($limits['top_value_components'])
            ->get();

        return [
            'stats' => $stats,
            'elementItems' => $elementItems,
            'kitItems' => $kitItems,
            'componentItems' => $componentItems,
            'lowStockItems' => $lowStockItems,
            'overStockItems' => $overStockItems,
            'recentMovements' => $recentMovements,
            'categoryDistribution' => $categoryDistribution,
            'topValueComponents' => $topValueComponents
        ];
    }

    private function calculatePercentageChanges(): array
    {
        // Aquí podrías implementar la lógica para calcular cambios porcentuales
        // Por ejemplo, comparar con el mes anterior
        return [
            'items_change' => 0,
            'value_change' => 0,
            'stock_change' => 0,
        ];
    }

    public function verifyAdminPassword(Request $request)
    {
        $request->validate([
            'admin_password' => 'required|string',
        ]);

        $admins = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            if (Hash::check($request->admin_password, $admin->password)) {
                return response()->json(['correct' => true], 200);
            }
        }

        return response()->json(['correct' => false], 403);
    }
}
