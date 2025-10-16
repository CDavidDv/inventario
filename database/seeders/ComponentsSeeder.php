<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Category;
use App\Models\User;

class ComponentsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener usuario admin
        $admin = User::role('admin')->first() ?? User::first();
        $electronicsCategory = Category::where('name', 'Electrónicos')->first();
        $mechanicsCategory = Category::where('name', 'Mecánicos')->first();
        $kitsCategory = Category::where('name', 'Kits Completos')->first();

        // Crear categorías si no existen
        if (!$electronicsCategory) {
            $electronicsCategory = Category::create([
                'name' => 'Electrónicos',
                'description' => 'Componentes electrónicos',
                'color' => '#3B82F6',
                'created_by' => $admin->id
            ]);
        }

        if (!$mechanicsCategory) {
            $mechanicsCategory = Category::create([
                'name' => 'Mecánicos',
                'description' => 'Componentes mecánicos',
                'color' => '#10B981',
                'created_by' => $admin->id
            ]);
        }

        if (!$kitsCategory) {
            $kitsCategory = Category::create([
                'name' => 'Kits Completos',
                'description' => 'Kits ensamblados',
                'color' => '#F59E0B',
                'created_by' => $admin->id
            ]);
        }

        // Componentes electrónicos
        $chasis = Item::create([
            'name' => 'Chasis Robokart',
            'description' => 'Chasis principal del robot kart',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 25.00,
            'location' => 'Estante A-1',
            'min_stock' => 5,
            'max_stock' => 50,
            'current_stock' => 15,
            'category_id' => $mechanicsCategory->id,
            'active' => true
        ]);

        $llantas = Item::create([
            'name' => 'Llantas Robokart',
            'description' => 'Llantas de goma para robot kart',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 8.50,
            'location' => 'Estante A-2',
            'min_stock' => 10,
            'max_stock' => 100,
            'current_stock' => 45,
            'category_id' => $mechanicsCategory->id,
            'active' => true
        ]);

        $motores = Item::create([
            'name' => 'Motor DC 12V',
            'description' => 'Motor de corriente directa 12V',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 15.00,
            'location' => 'Estante B-1',
            'min_stock' => 8,
            'max_stock' => 80,
            'current_stock' => 25,
            'category_id' => $electronicsCategory->id,
            'active' => true
        ]);

        $placaPCB = Item::create([
            'name' => 'Placa PCB Robokart',
            'description' => 'Placa de circuito impreso principal',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 12.00,
            'location' => 'Estante B-2',
            'min_stock' => 15,
            'max_stock' => 150,
            'current_stock' => 60,
            'category_id' => $electronicsCategory->id,
            'active' => true
        ]);

        $resistencia = Item::create([
            'name' => 'Resistencia 10k Ohm',
            'description' => 'Resistencia de 10k ohmios',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 0.10,
            'location' => 'Estante C-1',
            'min_stock' => 100,
            'max_stock' => 1000,
            'current_stock' => 450,
            'category_id' => $electronicsCategory->id,
            'active' => true
        ]);

        $picController = Item::create([
            'name' => 'PIC Controller 16F877A',
            'description' => 'Microcontrolador PIC 16F877A',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 8.00,
            'location' => 'Estante C-2',
            'min_stock' => 20,
            'max_stock' => 200,
            'current_stock' => 75,
            'category_id' => $electronicsCategory->id,
            'active' => true
        ]);

        $bluetoothModule = Item::create([
            'name' => 'Módulo Bluetooth HC-05',
            'description' => 'Módulo Bluetooth para comunicación',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 6.50,
            'location' => 'Estante C-3',
            'min_stock' => 15,
            'max_stock' => 150,
            'current_stock' => 30,
            'category_id' => $electronicsCategory->id,
            'active' => true
        ]);

        $housing = Item::create([
            'name' => 'Housing 4 Pines',
            'description' => 'Conector housing de 4 pines',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 0.50,
            'location' => 'Estante D-1',
            'min_stock' => 50,
            'max_stock' => 500,
            'current_stock' => 120,
            'category_id' => $electronicsCategory->id,
            'active' => true
        ]);

        $headers = Item::create([
            'name' => 'Headers Macho 2x4',
            'description' => 'Headers macho de 2x4 pines',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 0.25,
            'location' => 'Estante D-2',
            'min_stock' => 100,
            'max_stock' => 1000,
            'current_stock' => 300,
            'category_id' => $electronicsCategory->id,
            'active' => true
        ]);

        $manual = Item::create([
            'name' => 'Manual Robokart',
            'description' => 'Manual de usuario del robot kart',
            'type' => 'component',
            'unit' => 'pcs',
            'purchase_cost' => 2.00,
            'location' => 'Estante E-1',
            'min_stock' => 20,
            'max_stock' => 200,
            'current_stock' => 50,
            'category_id' => Category::where('name', 'Documentación')->first()->id ?? $electronicsCategory->id,
            'active' => true
        ]);

        // Crear Kit Robokart
        $kitRobokart = Item::create([
            'name' => 'Kit Robokart Completo',
            'description' => 'Kit completo para construir robot kart',
            'type' => 'kit',
            'unit' => 'kits',
            'purchase_cost' => 150.00,
            'location' => 'Estante K-1',
            'min_stock' => 3,
            'max_stock' => 30,
            'current_stock' => 8,
            'category_id' => $kitsCategory->id,
            'active' => true
        ]);

        // Crear Kit Tarjeta Programada
        $kitTarjeta = Item::create([
            'name' => 'Tarjeta Programada Robokart',
            'description' => 'Tarjeta electrónica programada para robokart',
            'type' => 'kit',
            'unit' => 'pcs',
            'purchase_cost' => 45.00,
            'location' => 'Estante K-2',
            'min_stock' => 10,
            'max_stock' => 100,
            'current_stock' => 25,
            'category_id' => $kitsCategory->id,
            'active' => true
        ]);

        $this->command->info('Componentes y kits creados exitosamente');
    }
}
