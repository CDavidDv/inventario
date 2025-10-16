<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\ElementPrice;
use App\Models\InventoryMovement;
use App\Models\User;

class SupplierTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunos proveedores de prueba
        $suppliers = [
            [
                'name' => 'TechSupply SA de CV',
                'address' => 'Av. Tecnológica 123, Ciudad Industrial',
                'phone' => '+52-55-1234-5678',
                'email' => 'ventas@techsupply.com',
                'website' => 'https://techsupply.com',
                'status' => 'active'
            ],
            [
                'name' => 'Materiales del Norte',
                'address' => 'Blvd. Industrial 456, Zona Norte',
                'phone' => '+52-81-9876-5432',
                'email' => 'contacto@materialesdelnorete.mx',
                'website' => 'https://materialesdelnorete.mx',
                'status' => 'active'
            ],
            [
                'name' => 'Proveeduría Global',
                'address' => 'Calle Comercio 789, Centro Comercial',
                'phone' => '+52-33-4567-8901',
                'email' => 'info@proveeiduria.com',
                'website' => null,
                'status' => 'active'
            ]
        ];

        foreach ($suppliers as $supplierData) {
            $supplier = Supplier::create($supplierData);
            
            // Obtener algunos items aleatorios para asignar precios
            $items = Item::inRandomOrder()->take(rand(3, 8))->get();
            
            foreach ($items as $item) {
                // Crear precios de elementos
                $purchasePrice = rand(50, 500) + (rand(0, 99) / 100);
                $sellingPrice = $purchasePrice * (1 + (rand(10, 50) / 100)); // Margen del 10% al 50%
                
                ElementPrice::create([
                    'item_id' => $item->id,
                    'supplier_id' => $supplier->id,
                    'purchase_price' => $purchasePrice,
                    'selling_price' => $sellingPrice,
                    'currency' => 'MXN',
                    'minimum_quantity' => rand(10, 100),
                    'lead_time_days' => rand(7, 30),
                    'notes' => 'Precio de prueba generado automáticamente',
                    'is_active' => true,
                    'is_preferred' => rand(0, 3) == 0, // 25% de probabilidad de ser preferido
                ]);
            }
            
            // Crear algunos movimientos de inventario (suministros) para los últimos 30 días
            $user = User::first();
            if ($user) {
                $itemsWithPrices = $supplier->elementPrices()->take(rand(2, 4))->get();
                
                foreach ($itemsWithPrices as $elementPrice) {
                    $item = $elementPrice->element;
                    
                    // Crear entre 1-3 suministros por item en los últimos 30 días
                    $suppliesCount = rand(1, 3);
                    
                    for ($i = 0; $i < $suppliesCount; $i++) {
                        $quantity = rand(10, 50);
                        $unitCost = $elementPrice->purchase_price ?? rand(20, 100);
                        $totalCost = $quantity * $unitCost;
                        $movementDate = now()->subDays(rand(1, 30));
                        
                        InventoryMovement::create([
                            'component_id' => $item->id,
                            'supplier_id' => $supplier->id,
                            'type' => 'in',
                            'concept' => 'Suministro de ' . $supplier->name,
                            'quantity' => $quantity,
                            'quantity_before' => $item->current_stock,
                            'quantity_after' => $item->current_stock + $quantity,
                            'unit_cost' => $unitCost,
                            'total_cost' => $totalCost,
                            'notes' => 'Movimiento de prueba - suministro del proveedor',
                            'reference' => 'SUP-' . strtoupper(substr($supplier->name, 0, 3)) . '-' . rand(1000, 9999),
                            'movement_date' => $movementDate,
                            'created_by' => $user->id,
                            'approved_by' => $user->id,
                            'approved_at' => $movementDate->addMinutes(30),
                        ]);
                        
                        // Actualizar el stock del item
                        $item->increment('current_stock', $quantity);
                    }
                }
            }
        }

        $this->command->info('✅ Datos de prueba de proveedores creados exitosamente:');
        $this->command->info('   - 3 proveedores con diferentes estados');
        $this->command->info('   - Precios de elementos asignados aleatoriamente');
        $this->command->info('   - Movimientos de inventario de suministros (últimos 30 días)');
        $this->command->info('   - Estadísticas calculables para validar el sistema');
    }
}
