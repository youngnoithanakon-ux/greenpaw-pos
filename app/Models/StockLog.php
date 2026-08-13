<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    // ใช้ created_at เป็น timestamp อัตโนมัติจาก DB และไม่มี updated_at
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'qty_before',
        'qty_change',
        'qty_after',
        'note',
    ];

    protected $casts = [
        'qty_before' => 'integer',
        'qty_change' => 'integer',
        'qty_after' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: A stock log belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Relationship: A stock log belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
