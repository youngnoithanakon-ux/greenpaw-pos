<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // ปิดการใช้งาน timestamps เนื่องจากใน DB ไม่มี created_at / updated_at สำหรับ table นี้
    public $timestamps = false;

    protected $fillable = [
        'category_name',
    ];

    /**
     * Relationship: A category has many products.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
