<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_out_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_out_transaction_id')->constrained('stock_out_transactions');
            $table->foreignId('raw_material_id')->constrained('raw_materials');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('qty', 15, 4);
            $table->decimal('qty_base', 15, 4);
            $table->decimal('cost_at_issue', 15, 4);
            $table->decimal('subtotal_hpp', 18, 2);
            $table->string('notes', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_out_details');
    }
};
