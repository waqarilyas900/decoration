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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->string('need_sewing')->nullable();
            $table->string('need_embroidery')->nullable();
            $table->string('need_imprinting')->nullable();
            $table->string('current_location');
            $table->string('created_by');
            $table->string('status')->default(0)->comment('0=pending&1=ready&3=removed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
