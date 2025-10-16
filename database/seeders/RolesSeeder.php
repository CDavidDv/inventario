<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles específicos para cada panel
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $supervisor = Role::create(['name' => 'supervisor', 'guard_name' => 'web']);
        $worker = Role::create(['name' => 'worker', 'guard_name' => 'web']);
        $user = Role::create(['name' => 'user', 'guard_name' => 'web']);

        // Crear permisos específicos para cada panel
        $permissions = [
            // Permisos generales
            'view_dashboard',
            'view_reports',
            
            // Permisos de administración
            'admin_access',
            'manage_users',
            'manage_roles',
            'manage_components',
            'manage_kits',
            'manage_categories',
            'view_all_movements',
            'export_data',
            
            // Permisos de supervisor
            'supervisor_access',
            'manage_stock_limits',
            'view_stock_analysis',
            'view_category_analysis',
            'export_stock_reports',
            
            // Permisos de trabajador
            'worker_access',
            'manage_stock_quantities',
            'view_worker_movements',
            'add_stock',
            'remove_stock',
            'adjust_stock',
            
            // Permisos de inventario general
            'manage_inventory',
            'view_inventory',
            'manage_clients',
            'manage_tickets',
            'manage_sales',
            'manage_personal',
            'manage_configuration'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Asignar permisos a roles
        $admin->givePermissionTo(Permission::all());
        
        $supervisor->givePermissionTo([
            'view_dashboard',
            'view_reports',
            'supervisor_access',
            'manage_stock_limits',
            'view_stock_analysis',
            'view_category_analysis',
            'export_stock_reports',
            'view_inventory',
            'manage_inventory'
        ]);

        $worker->givePermissionTo([
            'view_dashboard',
            'view_reports',
            'worker_access',
            'manage_stock_quantities',
            'view_worker_movements',
            'add_stock',
            'remove_stock',
            'adjust_stock',
            'view_inventory'
        ]);

        $user->givePermissionTo([
            'view_dashboard',
            'view_reports',
            'view_inventory'
        ]);
    }
}
