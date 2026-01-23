<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StockLedgerService
{
    public static function moveOut(int $productId, int $qty, string $refType, int $refId): void
    {
        self::record($productId, qtyIn: 0, qtyOut: $qty, refType: $refType, refId: $refId);
    }

    public static function moveIn(int $productId, int $qty, string $refType, int $refId): void
    {
        self::record($productId, qtyIn: $qty, qtyOut: 0, refType: $refType, refId: $refId);
    }

    protected static function record(int $productId, int $qtyIn, int $qtyOut, string $refType, int $refId): void
    {
        DB::transaction(function () use ($productId, $qtyIn, $qtyOut, $refType, $refId) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $balance = $product->qty_on_hand + $qtyIn - $qtyOut;

            DB::table('stock_ledger')->insert([
                'product_id'     => $productId,
                'ref_type'       => $refType,
                'ref_id'         => $refId,
                'qty_in'         => $qtyIn,
                'qty_out'        => $qtyOut,
                'balance_after'  => $balance,
                'created_at'     => now(),
            ]);

            $product->update(['qty_on_hand' => $balance]);
        });
    }
}
