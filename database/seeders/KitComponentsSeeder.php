<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\ItemItems;

class KitComponentsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener los kits
        $kitRobokart = Item::where('name', 'Kit Robokart Completo')->where('type', 'kit')->first();
        $kitTarjeta = Item::where('name', 'Tarjeta Programada Robokart')->where('type', 'kit')->first();
        
        // Obtener los componentes
        $chasis = Item::where('name', 'Chasis Robokart')->where('type', 'component')->first();
        $llantas = Item::where('name', 'Llantas Robokart')->where('type', 'component')->first();
        $motores = Item::where('name', 'Motor DC 12V')->where('type', 'component')->first();
        $manual = Item::where('name', 'Manual Robokart')->where('type', 'component')->first();
        $tarjeta = Item::where('name', 'Tarjeta Programada Robokart')->where('type', 'kit')->first();
        
        $placaPCB = Item::where('name', 'Placa PCB Robokart')->where('type', 'component')->first();
        $resistencia = Item::where('name', 'Resistencia 10k Ohm')->where('type', 'component')->first();
        $picController = Item::where('name', 'PIC Controller 16F877A')->where('type', 'component')->first();
        $bluetoothModule = Item::where('name', 'Módulo Bluetooth HC-05')->where('type', 'component')->first();
        $housing = Item::where('name', 'Housing 4 Pines')->where('type', 'component')->first();
        $headers = Item::where('name', 'Headers Macho 2x4')->where('type', 'component')->first();

        // Kit Robokart Completo contiene:
        if ($kitRobokart && $chasis) {
            ItemItems::create([
                'item_id' => $kitRobokart->id,
                'item_id_2' => $chasis->id,
                'quantity' => 1
            ]);
        }

        if ($kitRobokart && $llantas) {
            ItemItems::create([
                'item_id' => $kitRobokart->id,
                'item_id_2' => $llantas->id,
                'quantity' => 2
            ]);
        }

        if ($kitRobokart && $motores) {
            ItemItems::create([
                'item_id' => $kitRobokart->id,
                'item_id_2' => $motores->id,
                'quantity' => 2
            ]);
        }

        if ($kitRobokart && $manual) {
            ItemItems::create([
                'item_id' => $kitRobokart->id,
                'item_id_2' => $manual->id,
                'quantity' => 1
            ]);
        }

        if ($kitRobokart && $tarjeta) {
            ItemItems::create([
                'item_id' => $kitRobokart->id,
                'item_id_2' => $tarjeta->id,
                'quantity' => 1
            ]);
        }

        // Kit Tarjeta Programada contiene:
        if ($kitTarjeta && $placaPCB) {
            ItemItems::create([
                'item_id' => $kitTarjeta->id,
                'item_id_2' => $placaPCB->id,
                'quantity' => 1
            ]);
        }

        if ($kitTarjeta && $resistencia) {
            ItemItems::create([
                'item_id' => $kitTarjeta->id,
                'item_id_2' => $resistencia->id,
                'quantity' => 1
            ]);
        }

        if ($kitTarjeta && $picController) {
            ItemItems::create([
                'item_id' => $kitTarjeta->id,
                'item_id_2' => $picController->id,
                'quantity' => 1
            ]);
        }

        if ($kitTarjeta && $bluetoothModule) {
            ItemItems::create([
                'item_id' => $kitTarjeta->id,
                'item_id_2' => $bluetoothModule->id,
                'quantity' => 1
            ]);
        }

        if ($kitTarjeta && $housing) {
            ItemItems::create([
                'item_id' => $kitTarjeta->id,
                'item_id_2' => $housing->id,
                'quantity' => 1
            ]);
        }

        if ($kitTarjeta && $headers) {
            ItemItems::create([
                'item_id' => $kitTarjeta->id,
                'item_id_2' => $headers->id,
                'quantity' => 2
            ]);
        }

        $this->command->info('Relaciones de kits y componentes creadas exitosamente');
    }
}
