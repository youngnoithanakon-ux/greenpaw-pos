<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('plant_batches')) {
            Schema::create('plant_batches', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id');
                $table->integer('created_by');           // user_id ที่บันทึก
                $table->date('plant_date');              // วันที่เริ่มปลูก
                $table->date('expected_harvest_date');   // วันที่คาดว่าเก็บเกี่ยวได้
                $table->date('actual_harvest_date')->nullable(); // วันที่เก็บจริง
                $table->decimal('qty_planted', 10, 2);   // จำนวนที่ปลูก
                $table->decimal('qty_harvested', 10, 2)->nullable(); // จำนวนที่เก็บได้จริง
                $table->enum('status', ['growing', 'ready', 'harvested', 'failed'])
                      ->default('growing');
                $table->text('notes')->nullable();       // หมายเหตุ
                $table->integer('harvest_stock_log_id')->nullable(); // อ้างอิง StockLog ที่สร้างตอน harvest
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plant_batches');
    }
};
