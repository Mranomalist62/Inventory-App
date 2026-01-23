<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Produk Terlaris - {{ $startDate }} s/d {{ $endDate }}</title>
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
            border-bottom: 3px solid #f59e0b;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 22pt;
            font-weight: bold;
            color: #d97706;
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

        /* Stats Summary */
        .stats-summary {
            width: 100%;
            margin-bottom: 25px;
        }

        .stats-summary td {
            width: 50%;
            padding: 10px;
        }

        .stat-box {
            background: #fffbeb;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid #f59e0b;
        }

        .stat-label {
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 14pt;
            font-weight: bold;
            color: #d97706;
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
            background: #f59e0b;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 10pt;
            font-weight: 600;
        }

        table.data-table th.center {
            text-align: center;
        }

        table.data-table th.right {
            text-align: right;
        }

        table.data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10pt;
        }

        table.data-table td.center {
            text-align: center;
        }

        table.data-table td.right {
            text-align: right;
            font-weight: 600;
            color: #059669;
        }

        table.data-table tr:nth-child(even) {
            background: #fffbeb;
        }

        .rank {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 9pt;
        }

        .rank-1 {
            background: #fcd34d;
            color: #92400e;
        }

        .rank-2 {
            background: #d1d5db;
            color: #374151;
        }

        .rank-3 {
            background: #fdba74;
            color: #9a3412;
        }

        .rank-default {
            background: #e5e7eb;
            color: #6b7280;
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
            <div class="report-title">LAPORAN PRODUK TERLARIS</div>
            <div class="period">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d
                {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
            </div>
        </div>

        <!-- Stats Summary -->
        @php
            $totalQty = $data->sum('total_qty');
            $totalAmount = $data->sum('total_amount');
        @endphp
        <table class="stats-summary">
            <tr>
                <td>
                    <div class="stat-box">
                        <div class="stat-label">Total Produk Terjual</div>
                        <div class="stat-value">{{ number_format($totalQty, 0, ',', '.') }} unit</div>
                    </div>
                </td>
                <td>
                    <div class="stat-box">
                        <div class="stat-label">Total Nilai Penjualan</div>
                        <div class="stat-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Data Table -->
        <div class="section-title">Top 20 Produk Terlaris</div>
        @if(!empty($data) && count($data) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8%">Rank</th>
                        <th style="width: 47%">Nama Produk</th>
                        <th style="width: 20%" class="center">Jumlah Terjual</th>
                        <th style="width: 25%" class="right">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $row)
                        <tr>
                            <td class="center">
                                @php
                                    $rank = $index + 1;
                                    $rankClass = match ($rank) {
                                        1 => 'rank-1',
                                        2 => 'rank-2',
                                        3 => 'rank-3',
                                        default => 'rank-default'
                                    };
                                @endphp
                                <span class="rank {{ $rankClass }}">{{ $rank }}</span>
                            </td>
                            <td>{{ $row->product->name ?? '-' }}</td>
                            <td class="center">{{ number_format($row->total_qty, 0, ',', '.') }}</td>
                            <td class="right">Rp {{ number_format($row->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                Tidak ada data produk terlaris untuk periode ini
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB<br>
            Laporan ini digenerate secara otomatis oleh sistem POS Inventory
        </div>
    </div>
</body>

</html>