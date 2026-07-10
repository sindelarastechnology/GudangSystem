<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_number_counters', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 30);
            $table->string('period', 6);
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamp('updated_at')->nullable();

            $table->unique(['document_type', 'period'], 'uq_document_counter_type_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_number_counters');
    }
};
