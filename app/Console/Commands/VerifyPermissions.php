<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class VerifyPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:verify {--email= : Email del usuario a verificar} {--clear-cache : Limpiar cache de permisos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar permisos de usuarios y roles, y limpiar cache de permisos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE PERMISOS ===');
        $this->newLine();

        // Limpiar cache si se solicita
        if ($this->option('clear-cache')) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $this->info('✓ Cache de permisos limpiado');
            $this->newLine();
        }

        // Verificar un usuario específico
        if ($email = $this->option('email')) {
            $this->verifyUser($email);
            return 0;
        }

        // Mostrar información general
        $this->showGeneralInfo();

        return 0;
    }

    protected function verifyUser($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Usuario con email '{$email}' no encontrado");
            return;
        }

        $this->info("Usuario: {$user->name} ({$user->email})");
        $this->info("Roles: " . $user->roles->pluck('name')->implode(', '));
        $this->newLine();

        $hasManagePermissions = $user->hasPermissionTo('manage-permissions');

        if ($hasManagePermissions) {
            $this->info('✓ El usuario TIENE el permiso "manage-permissions"');
        } else {
            $this->error('✗ El usuario NO TIENE el permiso "manage-permissions"');

            // Verificar si el rol admin existe y tiene el permiso
            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole && !$adminRole->hasPermissionTo('manage-permissions')) {
                $this->warn('El rol "admin" no tiene el permiso "manage-permissions"');
                $this->info('Ejecuta: php artisan migrate para aplicar la corrección');
            }
        }
    }

    protected function showGeneralInfo()
    {
        // Mostrar roles
        $this->info('ROLES EXISTENTES:');
        $roles = Role::all();
        foreach ($roles as $role) {
            $this->line("  - {$role->name} ({$role->permissions->count()} permisos)");
        }
        $this->newLine();

        // Verificar permisos relacionados con manage
        $this->info('PERMISOS "MANAGE":');
        $managePermissions = Permission::where('name', 'like', '%manage%')->get();
        foreach ($managePermissions as $perm) {
            $this->line("  - {$perm->name}");
        }
        $this->newLine();

        // Verificar rol admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $hasPermission = $adminRole->hasPermissionTo('manage-permissions');
            $status = $hasPermission ? '✓' : '✗';
            $this->info("Rol 'admin' tiene 'manage-permissions': {$status}");
        } else {
            $this->warn('El rol "admin" no existe');
        }

        $this->newLine();
        $this->info('Para verificar un usuario específico:');
        $this->line('  php artisan permissions:verify --email=admin@example.com');
        $this->newLine();
        $this->info('Para limpiar cache de permisos:');
        $this->line('  php artisan permissions:verify --clear-cache');
    }
}
