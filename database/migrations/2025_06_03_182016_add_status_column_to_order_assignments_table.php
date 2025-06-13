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
        Schema::table('order_assignments', function (Blueprint $table) {
            $table->boolean('is_progress')->default(0)->after('section');
            $table->boolean('is_complete')->default(0)->after('section');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_assignments', function (Blueprint $table) {
            $table->dropColumn('is_progress');
            $table->dropColumn('is_complete');
        });
    }
};
