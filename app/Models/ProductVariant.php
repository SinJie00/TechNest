<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 
        'attributes', 
        'sku', 
        'price', 
        'discount_price',
        'stock', 
        'status', 
        'images'
    ];

    protected $casts = [
        'attributes' => 'array',
        'images' => 'array',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'status' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
