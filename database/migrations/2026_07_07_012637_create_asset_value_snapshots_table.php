<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_value_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('raw_material_id')->constrained('raw_materials');
            $table->decimal('qty', 15, 4);
            $table->decimal('avg_cost', 15, 4);
            $table->decimal('asset_value', 18, 2);
            $table->timestamp('created_at')->nullable();

            $table->index(['snapshot_date', 'warehouse_id'], 'idx_snapshot_date_wh');
            $table->index(['warehouse_id', 'raw_material_id', 'snapshot_date'], 'idx_snapshot_wh_item_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_value_snapshots');
    }
};
