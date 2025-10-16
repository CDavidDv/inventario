<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElementPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('element_prices', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('supplier')->onDelete('cascade');
            
            // Precios
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('MXN');
            
            // Información adicional
            $table->decimal('minimum_quantity', 12, 2)->nullable();
            $table->integer('lead_time_days')->nullable();
            $table->text('notes')->nullable();
            
            // Fechas de vigencia
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            
            // Estado
            $table->boolean('is_active')->default(true);
            $table->boolean('is_preferred')->default(false);
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['item_id', 'supplier_id']);
            $table->index(['supplier_id', 'is_active']);
            $table->index(['is_active', 'is_preferred']);
            
            // Constraint único para evitar duplicados
            $table->unique(['item_id', 'supplier_id'], 'unique_item_supplier_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('element_prices');
    }
}
