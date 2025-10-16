<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@inventario.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ]
        );

        // Asignar rol de administrador
        $adminRole = Role::findByName('Administrador');
        if ($adminRole && !$admin->hasRole('Administrador')) {
            $admin->assignRole($adminRole);
        }

        // Crear usuario gerente de inventario
        $manager = User::firstOrCreate(
            ['email' => 'gerente@inventario.com'],
            [
                'name' => 'Gerente de Inventario',
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ]
        );

        $managerRole = Role::findByName('Gerente de Inventario');
        if ($managerRole && !$manager->hasRole('Gerente de Inventario')) {
            $manager->assignRole($managerRole);
        }

        // Crear usuario operador
        $operator = User::firstOrCreate(
            ['email' => 'operador@inventario.com'],
            [
                'name' => 'Operador de Inventario',
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ]
        );

        $operatorRole = Role::findByName('Operador de Inventario');
        if ($operatorRole && !$operator->hasRole('Operador de Inventario')) {
            $operator->assignRole($operatorRole);
        }

        $this->command->info('Usuarios de prueba creados exitosamente:');
        $this->command->info('');
        $this->command->info('Administrador:');
        $this->command->info('  Email: admin@inventario.com');
        $this->command->info('  Password: password123');
        $this->command->info('');
        $this->command->info('Gerente de Inventario:');
        $this->command->info('  Email: gerente@inventario.com');
        $this->command->info('  Password: password123');
        $this->command->info('');
        $this->command->info('Operador de Inventario:');
        $this->command->info('  Email: operador@inventario.com');
        $this->command->info('  Password: password123');
    }
}
