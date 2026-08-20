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
            $table->string('io_group')->nullable()->after('area');
            $table->string('io_desc')->nullable()->after('io_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_transactions', function (Blueprint $table) {
            $table->dropColumn(['io_group', 'io_desc']);
        });
    }
};
