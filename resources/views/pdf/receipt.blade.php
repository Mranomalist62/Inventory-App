<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: monospace;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }

        .center {
            text-align: center;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
        }
    </style>
</head>

<body>
    <div class="center">
        <strong>PELARIYAN ID</strong><br>
        Jl. Sunan Ampel, Purwokerto<br>
        =========================
    </div>

    <table>
        <tr>
            <td>Kode</td>
            <td>: {{ $sale->code }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ $sale->date->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: {{ $sale->cashier->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <table>
        @foreach($sale->items as $item)
            <tr>
                <td colspan="2">{{ $item->product->name }}</td>
            </tr>
            <tr>
                <td>{{ $item->qty }} x {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td style="text-align:right">{{ number_format($item->line_total, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <div class="line"></div>
    <table>
        <tr>
            <td>Subtotal</td>
            <td style="text-align:right">{{ number_format($sale->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Diskon</td>
            <td style="text-align:right">{{ number_format($sale->discount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pajak</td>
            <td style="text-align:right">{{ number_format($sale->tax, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td style="text-align:right"><strong>{{ number_format($sale->grand_total, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Tunai</td>
            <td style="text-align:right">{{ number_format($sale->paid, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td style="text-align:right">{{ number_format($sale->change, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="line"></div>
    <div class="center">Terima kasih 🙏<br>Barang yang sudah dibeli tidak dapat ditukar</div>
</body>

</html>