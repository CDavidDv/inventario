<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            
            // Relación con el item/componente
            $table->foreignId('component_id')->constrained('items')->onDelete('cascade');
            
            // Tipo de movimiento
            $table->enum('type', ['in', 'out', 'adjustment', 'transfer', 'assembly', 'production', 'sale', 'return', 'loss'])->index();
            
            // Concepto del movimiento
            $table->string('concept')->index();
            
            // Cantidades
            $table->decimal('quantity', 12, 3);
            $table->decimal('quantity_before', 12, 3)->nullable();
            $table->decimal('quantity_after', 12, 3)->nullable();
            
            // Costos
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            
            // Información adicional
            $table->text('notes')->nullable();
            $table->string('reference')->nullable()->index();
            $table->string('batch_number')->nullable()->index();
            
            // Relaciones opcionales
            $table->foreignId('related_kit_id')->nullable()->constrained('items')->onDelete('set null');
            $table->foreignId('related_movement_id')->nullable()->constrained('inventory_movements')->onDelete('set null');
            
            // Metadata JSON para información adicional
            $table->json('metadata')->nullable();
            
            // Fechas
            $table->timestamp('movement_date')->useCurrent();
            
            // Usuario que creó el movimiento
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Aprobación (para movimientos que requieren autorización)
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Timestamps automáticos
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['component_id', 'type', 'movement_date']);
            $table->index(['created_by', 'movement_date']);
            $table->index(['type', 'concept']);
            $table->index(['movement_date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
