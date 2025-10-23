<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

echo "=== PERMISOS EN LA BASE DE DATOS ===\n\n";
$permissions = Permission::all();
foreach ($permissions as $perm) {
    echo "- {$perm->name}\n";
}

echo "\n=== ROLES Y SUS PERMISOS ===\n\n";
$roles = Role::with('permissions')->get();
foreach ($roles as $role) {
    echo "ROL: {$role->name}\n";
    foreach ($role->permissions as $perm) {
        echo "  - {$perm->name}\n";
    }
    echo "\n";
}

echo "=== USUARIOS ADMIN ===\n\n";
$admins = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'admin');
})->get(['id', 'name', 'email']);

foreach ($admins as $admin) {
    echo "Admin: {$admin->name} ({$admin->email})\n";
    echo "Permisos:\n";
    $perms = $admin->getAllPermissions();
    foreach ($perms as $perm) {
        echo "  - {$perm->name}\n";
    }
    echo "\n";
}
