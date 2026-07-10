<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_material_id')->constrained('raw_materials');
            $table->foreignId('from_unit_id')->constrained('units');
            $table->foreignId('to_unit_id')->constrained('units');
            $table->decimal('conversion_factor', 15, 4);
            $table->timestamps();

            $table->unique(['raw_material_id', 'from_unit_id', 'to_unit_id'], 'uq_unit_conversions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
