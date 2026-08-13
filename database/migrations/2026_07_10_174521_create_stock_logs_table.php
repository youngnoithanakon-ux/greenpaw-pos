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
        if (!Schema::hasTable('stock_logs')) {
            Schema::create('stock_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id');
                $table->integer('user_id');
                $table->enum('type', ['in', 'out']);
                $table->decimal('qty_before', 10, 2);
                $table->decimal('qty_change', 10, 2);
                $table->decimal('qty_after',  10, 2);
                $table->string('note', 255)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};
