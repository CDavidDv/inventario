<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "=== ASIGNANDO TODOS LOS PERMISOS AL ROL ADMIN ===\n\n";

// Obtener o crear el rol admin
$adminRole = Role::firstOrCreate(['name' => 'admin']);
echo "✓ Rol admin obtenido/creado\n\n";

// Obtener todos los permisos
$allPermissions = Permission::all();
echo "Total de permisos en la base de datos: " . $allPermissions->count() . "\n\n";

if ($allPermissions->count() === 0) {
    echo "⚠️  No hay permisos en la base de datos. Creando permisos básicos...\n\n";

    // Crear permisos básicos si no existen
    $basicPermissions = [
        'view_dashboard',
        'manage_items',
        'manage_categories',
        'manage_suppliers',
        'manage_stock',
        'view_reports',
        'manage_users',
        'manage_permissions',
        'manage_production',
        'view_analytics',
    ];

    foreach ($basicPermissions as $permName) {
        Permission::firstOrCreate(['name' => $permName]);
        echo "  ✓ Permiso creado: {$permName}\n";
    }

    // Volver a obtener todos los permisos
    $allPermissions = Permission::all();
    echo "\n✓ Permisos básicos creados\n\n";
}

// Sincronizar todos los permisos con el rol admin
$adminRole->syncPermissions($allPermissions);

echo "✓ TODOS los permisos han sido asignados al rol admin\n\n";

echo "=== PERMISOS ASIGNADOS AL ADMIN ===\n";
foreach ($allPermissions as $perm) {
    echo "  - {$perm->name}\n";
}

echo "\n=== USUARIOS CON ROL ADMIN ===\n";
$admins = \App\Models\User::role('admin')->get(['id', 'name', 'email']);

if ($admins->count() === 0) {
    echo "⚠️  No hay usuarios con rol admin\n";
} else {
    foreach ($admins as $admin) {
        echo "  ✓ {$admin->name} ({$admin->email})\n";
    }
}

echo "\n✅ PROCESO COMPLETADO\n";
