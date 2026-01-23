<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LaporanPenjualan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $title = 'Laporan Penjualan Harian';
    protected static string $view = 'filament.pages.laporan-penjualan';
    protected static ?string $navigationGroup = 'Laporan';

    public $startDate;
    public $endDate;
    public $report = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->generate();
    }

    public function generate()
    {
        if (!$this->startDate || !$this->endDate) {
            Notification::make()->title('Pilih rentang tanggal')->danger()->send();
            return;
        }

        $sales = Sale::query()
            ->whereBetween('date', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->get();

        $this->report = [
            'total_sales' => $sales->count(),
            'total_amount' => $sales->sum('grand_total'),
            'total_tax' => $sales->sum('tax'),
            'total_discount' => $sales->sum('discount'),
            'by_cashier' => $sales->groupBy('cashier_id')->map(fn($group) => [
                'name' => optional($group->first()->cashier)->name,
                'count' => $group->count(),
                'amount' => $group->sum('grand_total'),
            ]),
        ];
    }

    /* ===================== QUICK DATE METHODS ===================== */

    /** Set filter ke hari ini */
    public function setToday(): void
    {
        $this->startDate = now()->toDateString();
        $this->endDate = now()->toDateString();
        $this->generate();
    }

    /** Set filter ke kemarin */
    public function setYesterday(): void
    {
        $this->startDate = now()->subDay()->toDateString();
        $this->endDate = now()->subDay()->toDateString();
        $this->generate();
    }

    /** Set filter ke minggu ini */
    public function setThisWeek(): void
    {
        $this->startDate = now()->startOfWeek()->toDateString();
        $this->endDate = now()->endOfWeek()->toDateString();
        $this->generate();
    }

    /** Set filter ke bulan ini */
    public function setThisMonth(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->endOfMonth()->toDateString();
        $this->generate();
    }

    /** Set filter ke bulan lalu */
    public function setLastMonth(): void
    {
        $this->startDate = now()->subMonth()->startOfMonth()->toDateString();
        $this->endDate = now()->subMonth()->endOfMonth()->toDateString();
        $this->generate();
    }

    /* ===================== PDF EXPORT ===================== */

    /** Export laporan ke PDF */
    public function exportPdf()
    {
        if (!$this->startDate || !$this->endDate) {
            Notification::make()->title('Pilih rentang tanggal terlebih dahulu')->danger()->send();
            return;
        }

        // Ambil data sales untuk detail transaksi
        $sales = Sale::query()
            ->whereBetween('date', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->where('status', 'paid')
            ->with('cashier')
            ->orderBy('date', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.laporan-penjualan', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'report' => $this->report,
            'sales' => $sales,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'Laporan_Penjualan_' . $this->startDate . '_sd_' . $this->endDate . '.pdf';

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

