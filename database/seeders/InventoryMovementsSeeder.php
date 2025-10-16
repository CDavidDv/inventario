<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\User;

class InventoryMovementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo crear movimientos de ejemplo si no existen
        if (InventoryMovement::count() > 0) {
            $this->command->info('Inventory movements already exist, skipping seeder.');
            return;
        }

        // Obtener algunos items y usuarios para los movimientos de ejemplo
        $items = Item::limit(5)->get();
        $user = User::first();

        if ($items->isEmpty() || !$user) {
            $this->command->warn('No items or users found, skipping inventory movements seeder.');
            return;
        }

        $movements = [];

        foreach ($items as $item) {
            // Movimiento de entrada inicial
            $movements[] = [
                'component_id' => $item->id,
                'type' => 'in',
                'concept' => 'initial_stock',
                'quantity' => 100,
                'quantity_before' => 0,
                'quantity_after' => 100,
                'unit_cost' => 10.00,
                'total_cost' => 1000.00,
                'notes' => 'Stock inicial del item ' . $item->name,
                'movement_date' => now()->subDays(30),
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Movimiento de entrada adicional
            $movements[] = [
                'component_id' => $item->id,
                'type' => 'in',
                'concept' => 'purchase',
                'quantity' => 50,
                'quantity_before' => 100,
                'quantity_after' => 150,
                'unit_cost' => 12.00,
                'total_cost' => 600.00,
                'notes' => 'Compra adicional de ' . $item->name,
                'movement_date' => now()->subDays(15),
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Movimiento de salida
            $movements[] = [
                'component_id' => $item->id,
                'type' => 'out',
                'concept' => 'production_use',
                'quantity' => 20,
                'quantity_before' => 150,
                'quantity_after' => 130,
                'notes' => 'Usado en producción de componente',
                'movement_date' => now()->subDays(5),
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        InventoryMovement::insert($movements);

        $this->command->info('Created ' . count($movements) . ' inventory movement records.');
    }
}
