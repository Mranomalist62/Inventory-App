<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - {{ $startDate }} s/d {{ $endDate }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
        }

        .container {
            padding: 20px 30px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 22pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 14pt;
            color: #4b5563;
            margin-top: 8px;
        }

        .period {
            font-size: 10pt;
            color: #6b7280;
            margin-top: 5px;
        }

        /* Stats Grid */
        .stats-grid {
            width: 100%;
            margin-bottom: 25px;
        }

        .stats-grid td {
            width: 25%;
            padding: 10px;
            text-align: center;
        }

        .stat-box {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 15px 10px;
            border-left: 4px solid #2563eb;
        }

        .stat-box.green {
            border-left-color: #10b981;
        }

        .stat-box.amber {
            border-left-color: #f59e0b;
        }

        .stat-box.blue {
            border-left-color: #3b82f6;
        }

        .stat-label {
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 14pt;
            font-weight: bold;
            color: #111827;
        }

        .stat-value.green {
            color: #059669;
        }

        .stat-value.amber {
            color: #d97706;
        }

        .stat-value.blue {
            color: #2563eb;
        }

        /* Section */
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }

        /* Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.data-table th {
            background: #2563eb;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 10pt;
            font-weight: 600;
        }

        table.data-table th:last-child {
            text-align: right;
        }

        table.data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10pt;
        }

        table.data-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #059669;
        }

        table.data-table tr:nth-child(even) {
            background: #f9fafb;
        }

        table.data-table tr:hover {
            background: #f3f4f6;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: #9ca3af;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 9pt;
            color: #9ca3af;
            text-align: center;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">PELARIYAN ID</div>
            <div class="report-title">LAPORAN PENJUALAN</div>
            <div class="period">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d
                {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
            </div>
        </div>

        <!-- Summary Stats -->
        <table class="stats-grid">
            <tr>
                <td>
                    <div class="stat-box">
                        <div class="stat-label">Jumlah Transaksi</div>
                        <div class="stat-value">{{ number_format($report['total_sales'] ?? 0) }}</div>
                    </div>
                </td>
                <td>
                    <div class="stat-box green">
                        <div class="stat-label">Total Penjualan</div>
                        <div class="stat-value green">Rp {{ number_format($report['total_amount'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="stat-box amber">
                        <div class="stat-label">Total Diskon</div>
                        <div class="stat-value amber">Rp
                            {{ number_format($report['total_discount'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="stat-box blue">
                        <div class="stat-label">Total Pajak</div>
                        <div class="stat-value blue">Rp {{ number_format($report['total_tax'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Per Kasir -->
        <div class="section-title">Penjualan per Kasir</div>
        @if(!empty($report['by_cashier']) && count($report['by_cashier']) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50%">Nama Kasir</th>
                        <th style="width: 25%; text-align: center;">Jumlah Transaksi</th>
                        <th style="width: 25%">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['by_cashier'] as $row)
                        <tr>
                            <td>{{ $row['name'] ?? 'Unknown' }}</td>
                            <td class="text-center">{{ number_format($row['count'] ?? 0) }} transaksi</td>
                            <td>Rp {{ number_format($row['amount'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                Tidak ada data penjualan untuk periode ini
            </div>
        @endif

        <!-- Detail Transaksi (Opsional) -->
        @if(!empty($sales) && count($sales) > 0)
            <div class="section-title" style="margin-top: 25px;">Detail Transaksi</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%">Kode</th>
                        <th style="width: 20%">Tanggal</th>
                        <th style="width: 25%">Kasir</th>
                        <th style="width: 20%">Metode Bayar</th>
                        <th style="width: 20%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td>{{ $sale->code }}</td>
                            <td>{{ $sale->date->format('d/m/Y H:i') }}</td>
                            <td>{{ $sale->cashier->name ?? '-' }}</td>
                            <td style="text-transform: uppercase;">{{ $sale->payment_method ?? 'cash' }}</td>
                            <td>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Footer -->
        <div class="footer">
            Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB<br>
            Laporan ini digenerate secara otomatis oleh sistem POS Inventory
        </div>
    </div>
</body>

</html>