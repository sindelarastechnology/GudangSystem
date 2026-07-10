<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_material_id')->constrained('raw_materials');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->decimal('min_stock', 15, 4)->default(0);
            $table->decimal('current_stock', 15, 4)->default(0);
            $table->decimal('current_avg_cost', 15, 4)->default(0);
            $table->decimal('current_asset_value', 18, 2)->default(0);
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['raw_material_id', 'warehouse_id'], 'uq_material_stocks_item_warehouse');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_stocks');
    }
};
