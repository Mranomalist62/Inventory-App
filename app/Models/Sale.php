<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
     protected $fillable = [
        'code','date','cashier_id','customer_id','subtotal','tax','discount',
        'rounding','grand_total','paid','change','payment_method','status'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function items(){ return $this->hasMany(SaleItem::class); }
    public function cashier(){ return $this->belongsTo(User::class, 'cashier_id'); }
}
