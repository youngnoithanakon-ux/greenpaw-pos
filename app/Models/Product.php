<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'product_name',
        'image_path',
        'product_type',
        'cost_price',
        'sale_price',
        'consignment_fee',
        'stock_qty',
        'seed_cost',
        'soil_cost',
        'pot_cost',
        'labor_cost',
        'waste_percent',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'consignment_fee' => 'decimal:2',
        'stock_qty' => 'integer',
        'seed_cost' => 'decimal:2',
        'soil_cost' => 'decimal:2',
        'pot_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'waste_percent' => 'decimal:2',
        'status' => 'boolean',
    ];

    /**
     * Relationship: A product belongs to a category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Relationship: A product can be in many sale items.
     */
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }

    /**
     * Relationship: A product has many stock logs.
     */
    public function stockLogs()
    {
        return $this->hasMany(StockLog::class, 'product_id');
    }

    /**
     * Relationship: A product has many plant batches.
     */
    public function plantBatches()
    {
        return $this->hasMany(PlantBatch::class, 'product_id');
    }
}
