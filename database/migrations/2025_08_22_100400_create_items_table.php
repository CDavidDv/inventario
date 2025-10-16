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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['element', 'kit', 'component'])->default('element');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('description')->nullable();
            $table->string('unit')->default('unidad');
            $table->decimal('min_stock', 10, 2)->default(0);
            $table->decimal('max_stock', 10, 2)->default(0);
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->string('location')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('purchase_date')->nullable();
            $table->string('purchase_price')->nullable();
            $table->boolean('active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
