<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptController extends Controller
{
    public function print(Sale $sale)
    {
        $sale->load(['items.product', 'cashier']);

        $pdf = Pdf::loadView('pdf.receipt', [
            'sale' => $sale,
        ])->setPaper([0, 0, 226.77, 600.0], 'portrait'); // 80mm thermal

        return $pdf->stream("receipt-{$sale->code}.pdf");
    }
}
