<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\InventoryAlert;
use App\Models\InventoryMovement;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Laravel\Jetstream\Rules\Role;

class SupervisorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supervisor_access');
    }

    public function dashboard()
    {
        // Obtener todos los usuarios con sus roles
        $usuarios = User::with('roles')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'email_verified_at' => $user->email_verified_at,
                'active' => $user->active,
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name
                    ];
                }),
            ];
        });

        // Obtener todos los roles disponibles del sistema
        $roles = \Spatie\Permission\Models\Role::all()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions_count' => $role->permissions()->count()
            ];
        });
        
        return Inertia::render('Supervisor/Dashboard', [
            'users' => $usuarios,
            'roles' => $roles,
            'stats' => [
                'total_users' => $usuarios->count(),
                'total_roles' => $roles->count(),
                'users_with_roles' => $usuarios->filter(function ($user) {
                    return $user['roles']->count() > 0;
                })->count(),
                'users_without_roles' => $usuarios->filter(function ($user) {
                    return $user['roles']->count() === 0;
                })->count()
            ]
        ]);
    }

    public function inventory()
    {
        $components = Item::with(['category'])
            ->where('active', true)
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Supervisor/Inventory', [
            'components' => $components
        ]);
    }

    public function updateStockLimits(Request $request, Item $item)
    {
        $validated = $request->validate([
            'min_quantity' => 'required|integer|min:0',
            'max_quantity' => 'required|integer|min:min_quantity',
            'notes' => 'nullable|string'
        ]);

        $oldMin = $item->min_quantity;
        $oldMax = $item->max_quantity;

        $item->update([
            'min_quantity' => $validated['min_quantity'],
            'max_quantity' => $validated['max_quantity']
        ]);

        

        return back()->with('success', 'Límites de stock actualizados exitosamente');
    }

    public function bulkUpdateLimits(Request $request)
    {
        $validated = $request->validate([
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:components,id',
            'components.*.min_quantity' => 'required|integer|min:0',
            'components.*.max_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        $updatedCount = 0;

        foreach ($validated['components'] as $componentData) {
            $item = Item::find($componentData['id']);
            
            if ($item && $componentData['max_quantity'] >= $componentData['min_quantity']) {
                $oldMin = $item->min_quantity;
                $oldMax = $item->max_quantity;

                $item->update([
                    'min_quantity' => $componentData['min_quantity'],
                    'max_quantity' => $componentData['max_quantity']
                ]);

               

                $updatedCount++;
            }
        }

        return back()->with('success', "Se actualizaron {$updatedCount} componentes exitosamente");
    }

    public function stockAnalysis()
    {
        // Análisis de stock por categoría
        $categoryAnalysis = Item::withCount(['components as total_components'])
            ->withSum(['components as total_value' => function($query) {
                $query->select(DB::raw('SUM(current_quantity * cost)'));
            }])
            ->withCount(['components as low_stock' => function($query) {
                $query->where('current_quantity', '<=', 'min_quantity');
            }])
            ->withCount(['components as over_stock' => function($query) {
                $query->where('current_quantity', '>=', 'max_quantity');
            }])
            ->where('active', true)
            ->get();

        // Top 20 componentes por valor
        $topValueComponents = Item::select('name', 'id', 'current_quantity', 'cost', 'min_quantity', 'max_quantity')
            ->selectRaw('current_quantity * cost as total_value')
            ->where('active', true)
            ->orderBy('total_value', 'desc')
            ->limit(20)
            ->get();

        // Componentes críticos (stock muy bajo o muy alto)
        $criticalComponents = Item::with(['category'])
            ->where('active', true)
            ->where(function($query) {
                $query->where('current_quantity', '<=', DB::raw('min_quantity * 0.5'))
                      ->orWhere('current_quantity', '>=', DB::raw('max_quantity * 1.5'));
            })
            ->orderBy('current_quantity', 'asc')
            ->get();

        // Estadísticas de movimientos por mes (últimos 6 meses)
        $monthlyMovements = $this->getMonthlyMovementsData();

        return Inertia::render('Supervisor/StockAnalysis', [
            'categoryAnalysis' => $categoryAnalysis,
            'topValueComponents' => $topValueComponents,
            'criticalComponents' => $criticalComponents,
            'monthlyMovements' => $monthlyMovements
        ]);
    }

    public function categoryAnalysis()
    {
        $categories = Item::withCount(['components as total_components'])
            ->withSum(['components as total_value' => function($query) {
                $query->select(DB::raw('SUM(current_quantity * cost)'));
            }])
            ->withAvg(['components as avg_cost' => function($query) {
                $query->select('cost');
            }])
            ->withCount(['components as low_stock' => function($query) {
                $query->where('current_quantity', '<=', 'min_quantity');
            }])
            ->withCount(['components as over_stock' => function($query) {
                $query->where('current_quantity', '>=', 'max_quantity');
            }])
            ->where('active', true)
            ->get()
            ->map(function($item) {
                $item->utilization_rate = $item->total_components > 0 
                    ? round(($item->total_components - $item->low_stock - $item->over_stock) / $item->total_components * 100, 2)
                    : 0;
                return $item;
            })
            ->sortByDesc('total_value');

        // Distribución de stock por categoría
        $stockDistribution = Item::withCount(['components as total_components'])
            ->withSum(['components as total_stock' => function($query) {
                $query->select(DB::raw('SUM(current_quantity)'));
            }])
            ->where('active', true)
            ->get()
            ->pluck('total_stock', 'name')
            ->filter()
            ->toArray();

        return Inertia::render('Supervisor/CategoryAnalysis', [
            'categories' => $categories,
            'stockDistribution' => $stockDistribution
        ]);
    }

    public function exportStockReport()
    {
        $components = Item::with(['category'])
            ->where('active', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get();

        $filename = 'reporte_stock_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($components) {
            $file = fopen('php://output', 'w');
            
            // Headers del CSV
            fputcsv($file, [
                'Categoría', 'Código', 'Nombre', 'Stock Actual', 'Stock Mínimo', 
                'Stock Máximo', 'Costo Unitario', 'Valor Total', 'Estado'
            ]);

            foreach ($components as $item) {
                $status = '';
                if ($item->current_quantity <= $item->min_quantity) {
                    $status = 'STOCK BAJO';
                } elseif ($item->current_quantity >= $item->max_quantity) {
                    $status = 'SOBRE STOCK';
                } else {
                    $status = 'NORMAL';
                }

                fputcsv($file, [
                    $item->category->name ?? 'Sin Categoría',
                    $item->id,
                    $item->name,
                    $item->current_quantity,
                    $item->min_quantity,
                    $item->max_quantity,
                    $item->cost,
                    $item->current_quantity * $item->cost,
                    $status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Métodos para administrar usuarios
    public function toggleUserActive(User $user)
    {
        $user->update([
            'active' => !$user->active
        ]);

        return back()->with('success', 'Estado del usuario actualizado correctamente');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        // Actualizar datos básicos
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email']
        ]);

        // Actualizar contraseña solo si se proporcionó
        if (!empty($validated['password'])) {
            $user->update([
                'password' => bcrypt($validated['password'])
            ]);
        }

        return back()->with('success', 'Usuario actualizado correctamente');
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        // Crear el nuevo usuario
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'active' => true
        ]);

        // Asignar roles si se proporcionaron
        if (isset($validated['roles']) && !empty($validated['roles'])) {
            $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $validated['roles'])->pluck('name');
            $user->assignRole($roleNames);
        }

        return back()->with('success', 'Usuario creado correctamente');
    }

    public function updateUserRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        // Obtener los nombres de los roles por sus IDs
        $roleNames = \Spatie\Permission\Models\Role::whereIn('id', $validated['roles'] ?? [])->pluck('name');
        
        // Sincronizar los roles
        $user->syncRoles($roleNames);

        return back()->with('success', 'Roles del usuario actualizados correctamente');
    }

    public function deleteUser(User $user)
    {
        // Verificar que no sea el usuario actual
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propio usuario']);
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente');
    }

    public function show(User $user)
    {
        // Cargar datos básicos del usuario con roles
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'active' => $user->active,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'email_verified_at' => $user->email_verified_at,
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'guard_name' => $role->guard_name
                ];
            }),
        ];

        // Estadísticas generales del usuario
        $stats = [
            'total_system_logs' => SystemLog::where('user_id', $user->id)->count(),
            'total_inventory_movements' => InventoryMovement::where('created_by', $user->id)->count(),
            'days_since_creation' => $user->created_at->diffInDays(now()),
            'last_activity' => optional(SystemLog::where('user_id', $user->id)
                ->latest('created_at')
                ->first())->created_at,
        ];

        // Movimientos de inventario del usuario (últimos 50)
        $inventoryMovements = InventoryMovement::with(['component' => function($query) {
            $query->select('id', 'name', 'id');
        }])
        ->where('created_by', $user->id)
        ->select('id', 'component_id', 'type', 'quantity', 'notes', 'created_at')
        ->orderBy('created_at', 'desc')
        ->paginate(20, ['*'], 'movements_page');

        // Logs del sistema del usuario (últimos 50)
        $systemLogs = SystemLog::where('user_id', $user->id)
            ->select('id', 'action', 'description', 'loggable_type', 'loggable_id', 'ip_address', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'logs_page');

        // Estadísticas de actividad por tipo de acción (últimos 30 días)
        $activityStats = SystemLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();

        // Estadísticas de movimientos de inventario por tipo (últimos 30 días)
        $movementStats = InventoryMovement::where('created_by', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->select('type as movement_type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();

        // Actividad diaria (últimos 30 días)
        $dailyActivity = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayActivity = SystemLog::where('user_id', $user->id)
                ->whereDate('created_at', $date->toDateString())
                ->count();
            
            $dailyActivity[] = [
                'date' => $date->format('Y-m-d'),
                'count' => $dayActivity
            ];
        }

        // Items más manipulados por el usuario
        $topItems = InventoryMovement::with(['component' => function($query) {
                $query->select('id', 'name', 'id');
            }])
            ->where('created_by', $user->id)
            ->select('component_id', DB::raw('count(*) as total_movements'))
            ->groupBy('component_id')
            ->orderBy('total_movements', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Supervisor/UserShow', [
            'user' => $userData,
            'stats' => $stats,
            'inventoryMovements' => $inventoryMovements,
            'systemLogs' => $systemLogs,
            'activityStats' => $activityStats,
            'movementStats' => $movementStats,
            'dailyActivity' => $dailyActivity,
            'topItems' => $topItems
        ]);
    }

    private function getMonthlyMovementsData(): array
    {
        $months = [];
        $inMovements = [];
        $outMovements = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            
        }

        return [
            'labels' => $months,
            'inMovements' => $inMovements,
            'outMovements' => $outMovements
        ];
    }
}
