<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Barcode Siap Print</title>
  <style>
    * {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      box-sizing: border-box;
    }

    .gap-luar {
      display: flex;
      flex-wrap: wrap;
      gap: 2px;
    }

    .item {
      display: flex;
      width: calc(20% - 2px);
      padding: 5px;
      height: 115px;
      border: 2px solid black;
      align-items: center;
      justify-content: center;
      page-break-inside: avoid;
      break-inside: avoid;
    }

    .item-content {
      text-align: center;
    }

  </style>
</head>

<body>
  <div class="gap-luar">
    @foreach ($products as $item)
      <div class="item">
        <div class="item-content">
          <p style="font-size: 10px;margin:5px 0px;">{{ $item->name }} - Rp {{ number_format($item->sell_price, 0, ',', '.') }}</p>
          <span>{!! DNS1D::getBarcodeSVG($item->barcode, 'EAN13', 1.3, 30, 'black', false) !!}</span><br>
          <span style="font-size: 12px;">{{ $item->barcode }}</span>
        </div>
      </div>
    @endforeach
  </div>
</body>
<script>
  window.addEventListener('load', () => {
    setTimeout(() => {
      window.print();
    }, 300);
  });
</script>
</html>
