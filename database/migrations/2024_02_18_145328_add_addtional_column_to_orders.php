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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('sewing_progress')->default(0)->after('need_sewing');
            $table->string('embroidery_progress')->default(0)->after('need_embroidery');
            $table->string('imprinting_progress')->default(0)->after('need_imprinting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('sewing_progress');
            $table->dropColumn('embroidery_progress');
            $table->dropColumn('imprinting_progress');
        });
    }
};
