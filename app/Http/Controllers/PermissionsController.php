<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

class PermissionsController extends Controller
{
    public function __construct()
    {
        // Solo admins pueden administrar permisos
        $this->middleware('permission:manage-permissions');
    }

    /**
     * Mostrar vista de administración de permisos
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(function ($permission) {
            // Agrupar por módulo (primera parte del nombre)
            $parts = explode('-', $permission->name);
            return $parts[1] ?? 'general';
        });

        return Inertia::render('Admin/Permissions', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Actualizar permisos de un rol
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Sincronizar permisos del rol
        $role->syncPermissions($request->permissions);

        // Limpiar cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->back()->with('success', 'Permisos del rol actualizados correctamente');
    }

    /**
     * Obtener permisos del usuario autenticado para el frontend
     */
    public function getUserPermissions()
    {
        $user = auth()->user();
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'permissions' => $permissions
        ]);
    }
}
