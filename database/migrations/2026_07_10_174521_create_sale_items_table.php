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
        if (!Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->integer('sale_id')->nullable();
                $table->integer('product_id')->nullable();
                $table->decimal('qty', 10, 2)->nullable();
                $table->decimal('unit_cost', 10, 2)->nullable();
                $table->decimal('unit_price', 10, 2)->nullable();
                $table->decimal('consignment_fee', 10, 2)->default(0.00);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
