<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $cat = \App\Models\Category::firstOrCreate(['name' => 'Umum']);

        foreach (range(1,10) as $i) {
            \App\Models\Product::firstOrCreate(
                ['sku' => 'SKU'.str_pad($i,3,'0',STR_PAD_LEFT)],
                [
                    'barcode'     => null,
                    'name'        => "Produk $i",
                    'category_id' => $cat->id,
                    'unit'        => 'pcs',
                    'cost_price'  => 5000,
                    'sell_price'  => 10000 + ($i*500),
                    'tax_rate'    => 0,
                    'min_stock'   => 5,
                    'qty_on_hand' => 20,
                    'is_active'   => true,
                ]
            );
        }
    }
}
