<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Spatie\LaravelPdf\Facades\Pdf;
use Carbon\Carbon;

class BarcodeController extends Controller
{
    public function preview()
    {
        $ids = session('selected_product_ids');
        if ($ids) {
            $products = Product::whereIn('id', $ids)->select('name', 'sell_price', 'barcode')->get();
        } else {
            $products = Product::select('name', 'sell_price', 'barcode')->get();
        }
        return view('pdf.barcode-produk', compact('products'));
    }

    public function print()
    {
        $today = Carbon::today()->toDateString();
        $ids = session('selected_product_ids');
        if ($ids) {
            $products = Product::whereIn('id', $ids)->select('name', 'sell_price', 'barcode')->get();
        } else {
            $products = Product::select('name', 'sell_price', 'barcode')->get();
        }
        return Pdf::view('pdf.barcode-produk', [
            'products' => $products,
        ])
            ->format('A4')
            ->portrait()
            ->margins(10, 10, 10, 10) // mm
            ->download("barcode-{$today}.pdf");
    }
}
