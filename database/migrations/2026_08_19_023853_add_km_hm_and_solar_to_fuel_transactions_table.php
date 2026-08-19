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
        Schema::table('fuel_transactions', function (Blueprint $table) {
            $table->decimal('km_hm', 15, 3)->nullable()->after('code_unit');
            $table->decimal('solar', 15, 3)->nullable()->after('km_hm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_transactions', function (Blueprint $table) {
            $table->dropColumn(['km_hm', 'solar']);
        });
    }
};
