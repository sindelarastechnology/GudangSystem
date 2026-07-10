<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_material_id')->constrained('raw_materials');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->date('transaction_date');
            $table->enum('direction', ['in', 'out']);
            $table->string('source_type', 50);
            $table->unsignedBigInteger('source_id');
            $table->decimal('qty', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('running_qty_balance', 15, 4);
            $table->decimal('running_avg_cost', 15, 4);
            $table->decimal('running_asset_value', 18, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['raw_material_id', 'warehouse_id', 'transaction_date'], 'idx_ledger_item_warehouse_date');
            $table->index(['source_type', 'source_id'], 'idx_ledger_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');
    }
};
