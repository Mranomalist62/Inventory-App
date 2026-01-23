<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku','barcode','name','category_id','unit',
        'cost_price','sell_price','tax_rate','min_stock','qty_on_hand','is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tax_rate'  => 'decimal:2',
    ];

    public function category(){ return $this->belongsTo(Category::class); }
}
