<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'ventas@monkits.com',
            'password' => Hash::make('vBOsB5NJtt4dYLy'),
            'active' => true,
        ]);

        // Crear usuario supervisor
        $supervisor = User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@monkits.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        // Crear usuario trabajador
        $worker = User::create([
            'name' => 'Trabajador',
            'email' => 'worker@monkits.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        // Crear usuario básico
        $user = User::create([
            'name' => 'Usuario',
            'email' => 'user@monkits.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        // Asignar roles (los roles ya deben estar creados por RolesSeeder)
        $admin->assignRole('admin');
        $supervisor->assignRole('supervisor');
        $worker->assignRole('worker');
        $user->assignRole('user');
    }
}