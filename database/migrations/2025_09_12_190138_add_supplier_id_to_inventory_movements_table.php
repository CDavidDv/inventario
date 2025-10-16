<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierIdToInventoryMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            // Agregar referencia al proveedor que suministró el elemento
            $table->unsignedBigInteger('supplier_id')->nullable()->after('component_id')->constrained('supplier')->onDelete('set null');
            // Índice para consultas rápidas de suministros por proveedor
            $table->index(['supplier_id', 'type', 'movement_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex(['supplier_id', 'type', 'movement_date']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
}
