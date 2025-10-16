<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\User;
use App\Models\InventoryMovement;
use App\Models\KitItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:admin_access');
    }

    public function dashboard()
    {
        $stats = [
            'total_items' => Item::count(),
            'total_kits' => Item::where('type', 'kit')->count(),
            'total_users' => User::count(),
            'total_categories' => Category::count(),
            'total_movements' => InventoryMovement::count(),
            'total_value' => Item::sum(DB::raw('current_quantity * cost'))
        ];

        $recentMovements = InventoryMovement::with(['item', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $lowStockItems = Item::where('current_quantity', '<=', 'min_quantity')
            ->where('active', true)
            ->count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentMovements' => $recentMovements,
            'lowStockItems' => $lowStockItems
        ]);
    }

    // Gestión de Itemes
    public function items()
    {
        $items = Item::with(['category'])
            ->orderBy('name')
            ->paginate(20);

        $categories = Category::where('active', true)->get();

        return Inertia::render('Admin/Items/Index', [
            'items' => $items,
            'categories' => $categories
        ]);
    }

    public function createItem()
    {
        $categories = Category::where('active', true)->get();
        
        return Inertia::render('Admin/Items/Create', [
            'categories' => $categories
        ]);
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:items,code',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'cost' => 'required|numeric|min:0',
            'current_quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'max_quantity' => 'required|integer|min:min_quantity',
            'unit' => 'required|string|max:20',
            'type' => 'required|string|max:20',
            'active' => 'boolean'
        ]);

        $item = Item::create($validated);

        return redirect()->route('admin.items')
            ->with('success', 'Iteme creado exitosamente');
    }

    public function editItem(Item $item)
    {
        $categories = Category::where('active', true)->get();
        
        return Inertia::render('Admin/Items/Edit', [
            'item' => $item->load('category'),
            'categories' => $categories
        ]);
    }

    public function updateItem(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:items,code,' . $item->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'cost' => 'required|numeric|min:0',
            'current_quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'max_quantity' => 'required|integer|min:min_quantity',
            'unit' => 'required|string|max:20',
            'type' => 'required|string|max:20',
            'active' => 'boolean'
        ]);

        $item->update($validated);

        return redirect()->route('admin.items')
            ->with('success', 'Iteme actualizado exitosamente');
    }

    public function deleteItem(Item $item)
    {
        $item->update(['active' => false]);
        
        return redirect()->route('admin.items')
            ->with('success', 'Iteme desactivado exitosamente');
    }

    // Gestión de Kits
    public function kits()
    {
        $kits = Item::with(['category'])
            ->where('type', 'kit')
            ->orderBy('name')
            ->paginate(20);

        $items = Item::where('type', '!=', 'kit')
            ->where('active', true)
            ->get();

        return Inertia::render('Admin/Kits/Index', [
            'kits' => $kits,
            'items' => $items
        ]);
    }

    public function createKit()
    {
        $categories = Category::where('active', true)->get();
        $items = Item::where('type', '!=', 'kit')
            ->where('active', true)
            ->get();

        return Inertia::render('Admin/Kits/Create', [
            'categories' => $categories,
            'items' => $items
        ]);
    }

    public function storeKit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:items,code',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'cost' => 'required|numeric|min:0',
            'current_quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'max_quantity' => 'required|integer|min:min_quantity',
            'unit' => 'required|string|max:20',
            'type' => 'required|string|max:20',
            'active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $kitData = $validated;
        unset($kitData['items']);

        $kit = Item::create($kitData);

        // Crear relaciones con itemes
        foreach ($request->items as $itemData) {
            KitItem::create([
                'kit_id' => $kit->id,
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity']
            ]);
        }

        return redirect()->route('admin.kits')
            ->with('success', 'Kit creado exitosamente');
    }

    // Gestión de Usuarios
    public function users()
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->paginate(20);

        $roles = Role::all();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function createUser()
    {
        $roles = Role::all();
        
        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles
        ]);
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        $user->assignRole($validated['roles']);

        return redirect()->route('admin.users')
            ->with('success', 'Usuario creado exitosamente');
    }

    public function editUser(User $user)
    {
        $roles = Role::all();
        
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user->load('roles'),
            'roles' => $roles
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name'
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email']
        ]);

        if ($validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles($validated['roles']);

        return redirect()->route('admin.users')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'Usuario eliminado exitosamente');
    }

    // Log de Movimientos
    public function movements()
    {
        $movements = InventoryMovement::with(['item', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return Inertia::render('Admin/Movements/Index', [
            'movements' => $movements
        ]);
    }

    public function exportMovements()
    {
        $movements = InventoryMovement::with(['item', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'movimientos_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($movements) {
            $file = fopen('php://output', 'w');
            
            // Headers del CSV
            fputcsv($file, [
                'Fecha', 'Iteme', 'Tipo', 'Cantidad', 'Concepto', 
                'Usuario', 'IP', 'Referencia'
            ]);

            foreach ($movements as $movement) {
                fputcsv($file, [
                    $movement->created_at->format('Y-m-d H:i:s'),
                    $movement->item->name,
                    $movement->type,
                    $movement->quantity,
                    $movement->concept,
                    $movement->user->name,
                    $movement->ip_address,
                    $movement->reference
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
