<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Barcode Siap Print</title>
    <style>
        * {
            font-family: 'DejaVu Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 10mm;
        }

        /* === SIZE OPTIONS === */
        /* Adjust these values to change card sizing */
        :root {
            /* CARD DIMENSIONS */
            --card-width: 48mm;
            --card-height: 20mm;


            /* MARGINS & GAPS */
            --card-margin: 2mm;
            --card-padding: 2mm;

            /* BORDER */
            --card-border: 0.5mm solid #000;

            /* FONT SIZES */
            --name-font-size: 9px;
            --price-font-size: 10px;
            --barcode-font-size: 11px;
        }

        .grid-container {
            /* Calculate columns based on page width */
            width: 190mm;
            /* A4 width (210mm) minus margins (20mm) */

            /* Create invisible grid lines */
            display: block;
            position: relative;
        }

        /* Calculate exact positioning */
        .barcode-card {
            /* FIXED POSITIONING - No flexbox */
            position: absolute;
            width: var(--card-width);
            height: var(--card-height);
            border: var(--card-border);
            padding: var(--card-padding);
            text-align: center;
            page-break-inside: avoid;
        }

        /* Card content */
        .card-content {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .product-info {
            width: 100%;
            font-size: var(--name-font-size);
            line-height: 1.2;
            margin-bottom: 1mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .product-name {
            font-weight: bold;
        }

        .product-price {
            font-size: var(--price-font-size);
            color: #333;
        }

        .barcode-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
        }

        .barcode-image {
            display: inline-block;
            float: none !important;
            margin: 0 auto;
            max-width: 100%;
            max-height: 15mm;
            text-align: center;
        }

        .barcode-number {
            font-family: 'Courier New', monospace;
            font-size: var(--barcode-font-size);
            letter-spacing: 1px;
            margin-top: 1mm;
            font-weight: bold;
        }

        .no-barcode {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-style: italic;
            font-size: 10px;
        }

        .page-break {
            page-break-after: always;
            display: block;
            height: 0;
            width: 100%;
        }
    </style>
</head>

<body>
    @php
        // CALCULATE GRID DIMENSIONS
        $pageWidth = 190; // mm (A4 width minus margins)
        $pageHeight = 277; // mm (A4 height minus margins)

        $cardWidth = 48; // mm (--card-width)
        $cardHeight = 20; // mm (--card-height)
        $cardMargin = 10; // mm (--card-margin)

        // Calculate how many cards fit horizontally
        $cardsPerRow = floor($pageWidth / ($cardWidth + $cardMargin));

        // Calculate how many cards fit vertically
        $cardsPerColumn = floor($pageHeight / ($cardHeight + $cardMargin));

        // Total cards per page
        $cardsPerPage = $cardsPerRow * $cardsPerColumn;

        // Track current position
        $currentPage = 1;
        $currentRow = 0;
        $currentCol = 0;
        $itemCount = 0;
    @endphp

    <!-- Start first page -->
    <div class="grid-container" id="page-{{ $currentPage }}">
        @foreach ($products as $item)
            @php
                // Calculate position
                $left = $currentCol * ($cardWidth + $cardMargin);
                $top = $currentRow * ($cardHeight + $cardMargin);
            @endphp

            <div class="barcode-card" style="left: {{ $left }}mm; top: {{ $top }}mm;">
                <div class="card-content">
                    <!-- Product Info -->
                    <div class="product-info">
                        <div class="product-name">
                            {{ Str::limit($item->name, 25) }}
                        </div>
                        <div class="product-price">
                            Rp {{ number_format($item->sell_price, 0, ',', '.') }}
                        </div>
                    </div>

                    <!-- Barcode Area -->
                    <div class="barcode-area">
                        @if($item->barcode)
                            @php
                                $barcodeHTML = DNS1D::getBarcodeHTML($item->barcode, 'EAN13', 1.3, 30);
                            @endphp

                            <div class="barcode-image">
                                {!! $barcodeHTML !!}
                            </div>

                            <div class="barcode-number">
                                {{ $item->barcode }}
                            </div>
                        @else
                            <div class="no-barcode">
                                No Barcode
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @php
                // Move to next position
                $currentCol++;
                $itemCount++;

                // Check if row is full
                if ($currentCol >= $cardsPerRow) {
                    $currentCol = 0;
                    $currentRow++;

                    // Check if page is full
                    if ($currentRow >= $cardsPerColumn) {
                        $currentRow = 0;
                        $currentPage++;

                        // Close current page, start new one
                        if (!$loop->last) {
                            echo '</div><div class="page-break"></div><div class="grid-container" id="page-' . $currentPage . '">';
                        }
                    }
                }
            @endphp
        @endforeach
    </div>
</body>

</html>