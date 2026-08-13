<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantBatch extends Model
{
    protected $fillable = [
        'product_id',
        'created_by',
        'plant_date',
        'expected_harvest_date',
        'actual_harvest_date',
        'qty_planted',
        'qty_harvested',
        'status',
        'notes',
        'harvest_stock_log_id',
    ];

    protected $casts = [
        'plant_date'             => 'date',
        'expected_harvest_date'  => 'date',
        'actual_harvest_date'    => 'date',
        'qty_planted'            => 'decimal:2',
        'qty_harvested'          => 'decimal:2',
    ];

    // ---- Relationships ----

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stockLog()
    {
        return $this->belongsTo(StockLog::class, 'harvest_stock_log_id');
    }

    // ---- Accessors ----

    /** จำนวนวันที่เหลือจนถึงวันเก็บเกี่ยว (ลบถ้าเลยกำหนดแล้ว) */
    public function getDaysUntilHarvestAttribute(): int
    {
        if ($this->status !== 'growing' && $this->status !== 'ready') {
            return 0;
        }
        return (int) now()->startOfDay()->diffInDays($this->expected_harvest_date, false);
    }

    /** badge class สำหรับแต่ละ status */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'growing'   => '🌱 กำลังปลูก',
            'ready'     => '✅ พร้อมเก็บเกี่ยว',
            'harvested' => '📦 เก็บเกี่ยวแล้ว',
            'failed'    => '❌ ล้มเหลว',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'growing'   => '#3b82f6',
            'ready'     => '#22c55e',
            'harvested' => '#6b7280',
            'failed'    => '#ef4444',
            default     => '#6b7280',
        };
    }
}
