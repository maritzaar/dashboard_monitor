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
        Schema::table('data_alat', function (Blueprint $table) {
            $table->index('import_log_id', 'data_alat_import_log_id_index');
            $table->index(['bulan', 'tahun', 'id_aset', 'tanggal'], 'data_alat_period_asset_date_index');
            $table->index(['id_aset', 'bulan', 'tahun', 'tanggal'], 'data_alat_asset_period_date_index');
            $table->index(['bulan', 'tahun', 'internal_order'], 'data_alat_period_internal_order_index');
        });

        Schema::table('fuel_transactions', function (Blueprint $table) {
            $table->index(['bulan', 'tahun', 'internal_order'], 'fuel_transactions_period_internal_order_index');
            $table->index(['unit_code', 'bulan', 'tahun'], 'fuel_transactions_unit_period_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_transactions', function (Blueprint $table) {
            $table->dropIndex('fuel_transactions_unit_period_index');
            $table->dropIndex('fuel_transactions_period_internal_order_index');
        });

        Schema::table('data_alat', function (Blueprint $table) {
            $table->dropIndex('data_alat_period_internal_order_index');
            $table->dropIndex('data_alat_asset_period_date_index');
            $table->dropIndex('data_alat_period_asset_date_index');
            $table->dropIndex('data_alat_import_log_id_index');
        });
    }
};
