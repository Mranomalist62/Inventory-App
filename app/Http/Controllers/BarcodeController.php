<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BarcodeController extends Controller
{
    public function preview()
    {
        $ids = session('selected_product_ids');
        if ($ids) {
            $products = Product::whereIn('id', $ids)
                ->select('name', 'sell_price', 'barcode')
                ->get();
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
            $products = Product::whereIn('id', $ids)
                ->select('name', 'sell_price', 'barcode')
                ->get();
        } else {
            $products = Product::select('name', 'sell_price', 'barcode')->get();
        }

        // DomPDF syntax
        $pdf = Pdf::loadView('pdf.barcode-produk', [
            'products' => $products,
        ]);

        // Set paper and margins (in mm)
        $pdf->setPaper('A4', 'portrait');

        // Optional: Set custom margins if needed
        // Instead, use CSS in your Blade view

        return $pdf->download("barcode-{$today}.pdf");
    }
}


// namespace App\Http\Controllers;

// use App\Models\Product;
// use Spatie\LaravelPdf\Facades\Pdf;
// use Carbon\Carbon;

// class BarcodeController extends Controller
// {
//     public function preview()
//     {
//         $ids = session('selected_product_ids');
//         if ($ids) {
//             $products = Product::whereIn('id', $ids)->select('name', 'sell_price', 'barcode')->get();
//         } else {
//             $products = Product::select('name', 'sell_price', 'barcode')->get();
//         }
//         return view('pdf.barcode-produk', compact('products'));
//     }

//     public function print()
//     {
//         $today = Carbon::today()->toDateString();
//         $ids = session('selected_product_ids');
//         if ($ids) {
//             $products = Product::whereIn('id', $ids)->select('name', 'sell_price', 'barcode')->get();
//         } else {
//             $products = Product::select('name', 'sell_price', 'barcode')->get();
//         }
//         return Pdf::view('pdf.barcode-produk', [
//             'products' => $products,
//         ])
//             ->format('A4')
//             ->portrait()
//             ->margins(10, 10, 10, 10) // mm
//             ->download("barcode-{$today}.pdf");
//     }
// }
