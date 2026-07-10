<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('raw_materials');
            $table->decimal('system_qty', 15, 4);
            $table->foreignId('physical_qty_unit_id')->constrained('units');
            $table->decimal('physical_qty', 15, 4);
            $table->decimal('physical_qty_base', 15, 4);
            $table->decimal('difference_qty', 15, 4);
            $table->decimal('avg_cost_at_opname', 15, 4);
            $table->decimal('difference_value', 18, 2);
            $table->string('notes', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
    }
};
