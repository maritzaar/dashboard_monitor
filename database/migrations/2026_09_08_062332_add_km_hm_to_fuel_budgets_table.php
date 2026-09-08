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
        Schema::table('fuel_budgets', function (Blueprint $table) {
            $table->string('km_hm', 20)->nullable()->after('group_internal_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_budgets', function (Blueprint $table) {
            $table->dropColumn('km_hm');
        });
    }
};
