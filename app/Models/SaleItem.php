<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id','product_id','qty','unit_price','discount','tax','line_total'
    ];

    public function sale(){ return $this->belongsTo(Sale::class); }
    public function product(){ return $this->belongsTo(Product::class); }
}
