<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Category;
use App\Models\User;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener o crear un usuario por defecto para created_by
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Default',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Obtener o crear una categoría por defecto
        $category = Category::firstOrCreate(
            ['name' => 'Electrónicos'],
            [
                'description' => 'Componentes electrónicos',
                'color' => '#3B82F6',
                'created_by' => $user->id
            ]
        );

        // Crear items de ejemplo
        $items = [
            [
                'name' => 'Resistencia 1K Ohm',
                'description' => 'Resistencia de carbón 1/4W 1K Ohm',
                'type' => 'component',
                'category_id' => $category->id,
                'unit' => 'Pieza',
                'min_stock' => 100,
                'max_stock' => 1000,
                'current_stock' => 50,
                'purchase_cost' => 0.50,
                'sale_price' => 1.00,
                'location' => 'Estante A1',
                'active' => true
            ],
            [
                'name' => 'Capacitor 100µF',
                'description' => 'Capacitor electrolítico 100µF 25V',
                'type' => 'component',
                'category_id' => $category->id,
                'unit' => 'Pieza',
                'min_stock' => 50,
                'max_stock' => 500,
                'current_stock' => 25,
                'purchase_cost' => 1.20,
                'sale_price' => 2.50,
                'location' => 'Estante A2',
                'active' => true
            ],
            [
                'name' => 'Arduino Uno R3',
                'description' => 'Microcontrolador Arduino Uno Rev3',
                'type' => 'component',
                'category_id' => $category->id,
                'unit' => 'Pieza',
                'min_stock' => 10,
                'max_stock' => 50,
                'current_stock' => 15,
                'purchase_cost' => 15.00,
                'sale_price' => 25.00,
                'location' => 'Vitrina B1',
                'active' => true
            ]
        ];

        foreach ($items as $itemData) {
            Item::create($itemData);
        }

        $this->command->info('Items de ejemplo creados exitosamente: ' . count($items) . ' items');
    }
}
