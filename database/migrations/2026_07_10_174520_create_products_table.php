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
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->integer('category_id')->nullable();
                $table->string('product_name', 255);
                $table->string('image_path', 255)->nullable();
                $table->enum('product_type', ['produced', 'bought'])->default('produced');
                $table->decimal('cost_price', 10, 2)->default(0.00);
                $table->decimal('sale_price', 10, 2);
                $table->decimal('consignment_fee', 10, 2)->default(0.00);
                $table->integer('stock_qty')->default(0);
                $table->decimal('seed_cost', 10, 2)->default(0.00);
                $table->decimal('soil_cost', 10, 2)->default(0.00);
                $table->decimal('pot_cost', 10, 2)->default(0.00);
                $table->decimal('labor_cost', 10, 2)->default(0.00);
                $table->decimal('waste_percent', 10, 2)->default(0.00);
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
