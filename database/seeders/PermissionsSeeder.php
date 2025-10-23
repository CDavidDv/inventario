<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir todos los permisos del sistema
        $permissions = [
            // Dashboard
            'view-dashboard' => 'Ver Dashboard',

            // Inventario
            'view-inventory' => 'Ver Inventario',
            'create-inventory' => 'Crear Items de Inventario',
            'edit-inventory' => 'Editar Items de Inventario',
            'delete-inventory' => 'Eliminar Items de Inventario',
            'adjust-stock' => 'Ajustar Stock',
            'view-stock-alerts' => 'Ver Alertas de Stock',

            // Producción
            'view-production' => 'Ver Producción',
            'create-production-order' => 'Crear Orden de Producción',
            'edit-production-order' => 'Editar Orden de Producción',
            'delete-production-order' => 'Eliminar Orden de Producción',
            'complete-production-order' => 'Completar Orden de Producción',
            'cancel-production-order' => 'Cancelar Orden de Producción',

            // Distribuidores/Proveedores
            'view-suppliers' => 'Ver Distribuidores',
            'create-supplier' => 'Crear Distribuidor',
            'edit-supplier' => 'Editar Distribuidor',
            'delete-supplier' => 'Eliminar Distribuidor',

            // Movimientos de Inventario
            'view-movements' => 'Ver Movimientos de Inventario',
            'export-movements' => 'Exportar Movimientos',

            // Supervisor
            'view-supervisor-dashboard' => 'Ver Dashboard de Supervisor',
            'manage-users' => 'Administrar Usuarios',
            'create-user' => 'Crear Usuarios',
            'edit-user' => 'Editar Usuarios',
            'delete-user' => 'Eliminar Usuarios',
            'assign-roles' => 'Asignar Roles',
            'toggle-user-status' => 'Activar/Desactivar Usuarios',

            // Auditoría
            'view-system-logs' => 'Ver Logs del Sistema',
            'export-system-logs' => 'Exportar Logs del Sistema',

            // Administración de Permisos
            'manage-permissions' => 'Administrar Permisos',
            'assign-permissions' => 'Asignar Permisos a Roles',
            'view-permissions' => 'Ver Permisos',

            // Reportes
            'view-reports' => 'Ver Reportes',
            'export-reports' => 'Exportar Reportes',
            'view-analytics' => 'Ver Análisis y Estadísticas',

            // Categorías
            'manage-categories' => 'Administrar Categorías',

            // Configuración
            'view-settings' => 'Ver Configuración',
            'edit-settings' => 'Editar Configuración',

            ''
        ];

        // Crear permisos
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        // Asignar permisos a roles

        // Admin: tiene todos los permisos
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Supervisor: permisos de supervisión y gestión de usuarios
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisorRole->givePermissionTo([
            'view-dashboard',
            'view-inventory',
            'edit-inventory',
            'adjust-stock',
            'view-stock-alerts',
            'view-production',
            'edit-production-order',
            'complete-production-order',
            'view-suppliers',
            'edit-supplier',
            'view-movements',
            'export-movements',
            'view-supervisor-dashboard',
            'manage-users',
            'create-user',
            'edit-user',
            'assign-roles',
            'toggle-user-status',
            'view-reports',
            'export-reports',
            'view-analytics',
        ]);

        // Worker: permisos básicos de trabajo
        $workerRole = Role::firstOrCreate(['name' => 'worker']);
        $workerRole->givePermissionTo([
            'view-dashboard',
            'view-inventory',
            'create-inventory',
            'edit-inventory',
            'adjust-stock',
            'view-stock-alerts',
            'view-production',
            'create-production-order',
            'edit-production-order',
            'complete-production-order',
            'view-suppliers',
            'view-movements',
        ]);

        $this->command->info('Permisos creados y asignados exitosamente!');
    }
}
