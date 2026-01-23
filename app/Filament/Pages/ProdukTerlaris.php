<?php

namespace App\Filament\Pages;

use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProdukTerlaris extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Produk Terlaris';
    protected static ?string $title = 'Laporan Produk Terlaris';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.produk-terlaris';

    public $startDate;
    public $endDate;
    public $data = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->generate();
    }

    public function generate()
    {
        $this->data = SaleItem::select(
            'product_id',
            DB::raw('SUM(qty) as total_qty'),
            DB::raw('SUM(line_total) as total_amount')
        )
            ->whereHas('sale', function ($q) {
                $q->whereBetween('date', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
                    ->where('status', 'paid');
            })
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->get();
    }

    /* ===================== PDF EXPORT ===================== */

    /** Export laporan produk terlaris ke PDF */
    public function exportPdf()
    {
        if (!$this->startDate || !$this->endDate) {
            Notification::make()->title('Pilih rentang tanggal terlebih dahulu')->danger()->send();
            return;
        }

        // Ambil data fresh untuk PDF
        $pdfData = SaleItem::select(
            'product_id',
            DB::raw('SUM(qty) as total_qty'),
            DB::raw('SUM(line_total) as total_amount')
        )
            ->whereHas('sale', function ($q) {
                $q->whereBetween('date', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
                    ->where('status', 'paid');
            })
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->get();

        if ($pdfData->isEmpty()) {
            Notification::make()->title('Tidak ada data untuk periode ini')->warning()->send();
            return;
        }

        $pdf = Pdf::loadView('pdf.produk-terlaris', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'data' => $pdfData,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Produk_Terlaris_' . $this->startDate . '_sd_' . $this->endDate . '.pdf';

        Notification::make()
            ->title('PDF berhasil diunduh')
            ->success()
            ->send();

        return response()->streamDownload(
            fn() => print ($pdf->output()),
            $filename
        );
    }
}
