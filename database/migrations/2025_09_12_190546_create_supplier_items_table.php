<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_items', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('supplier_id')->constrained('supplier')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            
            // Información del producto asignado
            $table->boolean('is_primary_supplier')->default(false); // Proveedor principal para este item
            $table->decimal('preferred_stock_level', 12, 2)->nullable(); // Nivel de stock preferido que debe mantener
            $table->integer('priority')->default(1); // Prioridad (1 = más alta)
            $table->text('notes')->nullable();
            
            // Estado
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Índices
            $table->index(['supplier_id', 'is_active']);
            $table->index(['item_id', 'is_primary_supplier']);
            $table->index(['is_active', 'priority']);
            
            // Constraint único para evitar duplicados
            $table->unique(['supplier_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_items');
    }
}
