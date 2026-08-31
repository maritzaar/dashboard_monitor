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
        Schema::create('fuel_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->nullable()->constrained('import_logs')->onDelete('cascade');
            $table->integer('tahun')->default(2026);
            $table->string('bulan', 20);
            $table->string('group_aset', 50)->nullable();
            $table->string('area', 50)->nullable();
            $table->string('pt', 50)->nullable();
            $table->string('unit_code', 50)->nullable();
            $table->string('satuan', 30)->nullable();
            $table->string('owner', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('internal_order', 50)->nullable();
            $table->string('group_internal_order', 100)->nullable();
            $table->decimal('output_budget', 15, 3)->default(0);
            $table->decimal('output_actual', 15, 3)->default(0);
            $table->decimal('solar_budget', 15, 3)->default(0);
            $table->decimal('solar_actual', 15, 3)->default(0);
            $table->timestamps();

            $table->index(['tahun', 'bulan', 'unit_code']);
            $table->index(['tahun', 'bulan', 'internal_order']);
            $table->index(['group_internal_order']);
            $table->index(['pt', 'area']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_budgets');
    }
};
