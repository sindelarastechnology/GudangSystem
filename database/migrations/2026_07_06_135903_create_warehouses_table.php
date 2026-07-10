<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->string('location', 200)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('locked_by_opname_id')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // FK ke stock_opnames akan ditambahkan setelah tabel stock_opnames dibuat (Fase 5)
            // $table->foreign('locked_by_opname_id')->references('id')->on('stock_opnames')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
