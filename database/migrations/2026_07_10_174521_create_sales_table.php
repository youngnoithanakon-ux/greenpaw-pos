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
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->nullable();
                $table->timestamp('sale_date')->useCurrent();
                $table->decimal('total_amount', 10, 2)->nullable();
                $table->decimal('total_cost', 10, 2)->nullable();
                $table->decimal('total_consignment_fee', 10, 2)->default(0.00);
                $table->enum('status', ['normal', 'void'])->default('normal');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
