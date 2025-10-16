<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InventoryPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos para Elementos
        $elementPermissions = [
            'view elements',
            'create elements', 
            'edit elements',
            'delete elements',
            'manage elements stock'
        ];

        // Crear permisos para Proveedores
        $supplierPermissions = [
            'view suppliers',
            'create suppliers',
            'edit suppliers', 
            'delete suppliers'
        ];

        // Crear permisos para Precios de Elementos
        $pricePermissions = [
            'view element prices',
            'create element prices',
            'edit element prices',
            'delete element prices'
        ];

        // Crear permisos para Kits
        $kitPermissions = [
            'view kits',
            'create kits',
            'edit kits',
            'delete kits',
            'assemble kits'
        ];

        // Crear permisos para Movimientos de Inventario
        $movementPermissions = [
            'view inventory movements',
            'create inventory movements',
            'edit inventory movements',
            'delete inventory movements',
            'approve inventory movements'
        ];

        // Crear permisos para Logs de Actividad
        $logPermissions = [
            'view activity logs'
        ];

        // Crear permisos para Alertas de Stock
        $alertPermissions = [
            'view stock alerts',
            'manage stock alerts'
        ];

        // Crear permisos de Dashboard
        $dashboardPermissions = [
            'view dashboard',
            'view reports',
            'export reports'
        ];

        // Crear permisos de Administración
        $adminPermissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'access admin panel'
        ];

        // Crear permisos de Reportes
        $reportPermissions = [
            'view reports',
            'export reports',
            'view analytics',
            'configure reports'
        ];

        // Combinar todos los permisos
        $allPermissions = array_merge(
            $elementPermissions,
            $supplierPermissions,
            $pricePermissions,
            $kitPermissions,
            $movementPermissions,
            $logPermissions,
            $alertPermissions,
            $dashboardPermissions,
            $adminPermissions,
            $reportPermissions
        );

        // Crear todos los permisos
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $managerRole = Role::firstOrCreate(['name' => 'Gerente de Inventario']);
        $operatorRole = Role::firstOrCreate(['name' => 'Operador de Inventario']);
        $viewerRole = Role::firstOrCreate(['name' => 'Consultor']);

        // Asignar permisos al Administrador (todos los permisos)
        $adminRole->givePermissionTo($allPermissions);

        // Asignar permisos al Gerente de Inventario
        $managerPermissions = array_merge(
            $elementPermissions,
            $supplierPermissions,
            $pricePermissions,
            $kitPermissions,
            $movementPermissions,
            $logPermissions,
            $alertPermissions,
            $dashboardPermissions,
            ['manage users'] // Solo gestión de usuarios, no roles/permisos
        );
        $managerRole->givePermissionTo($managerPermissions);

        // Asignar permisos al Operador de Inventario
        $operatorPermissions = array_merge(
            ['view elements', 'edit elements', 'manage elements stock'],
            ['view suppliers'],
            ['view element prices'],
            ['view kits', 'assemble kits'],
            ['view inventory movements', 'create inventory movements'],
            ['view stock alerts'],
            ['view dashboard']
        );
        $operatorRole->givePermissionTo($operatorPermissions);

        // Asignar permisos al Consultor (solo lectura)
        $viewerPermissions = [
            'view elements',
            'view suppliers', 
            'view element prices',
            'view kits',
            'view inventory movements',
            'view activity logs',
            'view stock alerts',
            'view dashboard',
            'view reports'
        ];
        $viewerRole->givePermissionTo($viewerPermissions);

        $this->command->info('Roles y permisos creados exitosamente:');
        $this->command->info('- Administrador: Acceso completo');
        $this->command->info('- Gerente de Inventario: Gestión completa de inventario');
        $this->command->info('- Operador de Inventario: Operaciones básicas de inventario');
        $this->command->info('- Consultor: Solo lectura');
    }
}
