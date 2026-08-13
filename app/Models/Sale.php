<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    // ปิด default timestamps เนื่องจากเราใช้ sale_date เป็นหลัก
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'sale_date',
        'total_amount',
        'total_cost',
        'total_consignment_fee',
        'received_amount',
        'status',
    ];

    protected $casts = [
        'sale_date'            => 'datetime',
        'total_amount'         => 'decimal:2',
        'total_cost'           => 'decimal:2',
        'total_consignment_fee'=> 'decimal:2',
        'received_amount'      => 'decimal:2',
    ];

    /**
     * Relationship: A sale belongs to a user (staff or admin).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: A sale contains many items.
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }
}
