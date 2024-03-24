@php
  $currentFacultad = '';
  $counter = 0;
  $totalSum = [];
  $total = 0;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Reporte</title>
  <style>
    * {
      font-family: Helvetica;
    }

    @page {
      margin: 165px 20px 20px 20px;
    }

    .head-1 {
      position: fixed;
      top: -135px;
      left: 0px;
      height: 90px;
    }

    .head-1 img {
      margin-left: 280px;
      background: red;
      height: 85px;
    }

    .head-2 {
      position: fixed;
      top: -135px;
      right: 0;
    }

    .head-2 p {
      text-align: right;
    }

    .head-2 .rais {
      font-size: 11px;
      margin-bottom: 0;
    }

    .head-2 .fecha {
      font-size: 5px;
      margin-top: 0;
    }

    .head-2 .user {
      font-size: 11px;
      margin-top: 0;
    }

    .foot-1 {
      position: fixed;
      bottom: 0px;
      left: 0px;
      text-align: left;
      font-size: 11px;
      font-style: oblique;
    }

    .div {
      position: fixed;
      top: -45px;
      width: 100%;
      height: 0.5px;
      background: #000;
    }

    .titulo {
      width: 1083px;
      font-size: 16px;
      text-align: center;
    }

    .table1 {
      width: 100%;
      border-collapse: separate;
      margin-bottom: 30px;
    }

    .table1>tbody {
      border-bottom: 1.5px solid #000;
    }

    .table1>thead {
      margin-top: -1px;
      font-size: 11px;
      border-top: 1.5px solid #000;
      border-bottom: 1.5px solid #000;
    }

    .table1>thead th {
      font-weight: normal;
    }

    .table1>tbody td {
      font-size: 11px;
      text-align: center;
      padding-top: 2px;
    }

    .table1>tfoot {
      margin-top: -1px;
      font-size: 11px;
      text-align: center;
    }

    .extra-1 {
      font-size: 11px;
      text-align: left;
      width: 100%;
    }

    .extra-2 {
      font-size: 11px;
      text-align: right;
      width: 100%;
    }

    .extra-firma {
      font-size: 11px;
      text-align: center;
      width: 100%;
    }
  </style>
</head>

<body>
  <div class="head-1">
    <img src="{{ public_path('head-pdf.jpg') }}" alt="Header">
    <p class="titulo">
      <strong>
        Consolidado general de proyectos - {{ $periodo }}
      </strong>
    </p>
  </div>
  <div class="head-2">
    <p class="rais">© RAIS</p>
    <p class="fecha">
      Fecha: {{ date('d/m/Y') }}<br>
      Hora: {{ date('H:i:s') }}
    </p>
    <br>
    <p class="user">
      ichajaya
    </p>
  </div>
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <div class="cuerpo">
    <!--  Listado  -->
    @foreach ($proyectos as $item)
      @if ($loop->first)
        <table class="table1">
          <thead>
            <tr>
              <th>Facultad</th>
              @foreach ($tipos as $tipo)
                <th>{{ $tipo->tipo_proyecto }}</th>
              @endforeach
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
      @endif
      <tr>
        <td style="text-align: left; padding-left: 10px;">{{ $item->facultad }}</td>
        @foreach ($tipos as $tipo)
          @php
            $col = $tipo->tipo_proyecto;
            if (!isset($totalSum[$col])) {
                $totalSum[$col] = 0;
            }
            $totalSum[$col] += $item->$col;
          @endphp
          <td>{{ $item->$col }}</td>
        @endforeach
        <td>{{ $item->total_cuenta }}</td>
        @php
          $total += $item->total_cuenta;
        @endphp
      </tr>
      @if ($loop->last)
        </tbody>
        <tfoot>
          <tr>
            <td>Total</td>
            @foreach ($tipos as $tipo)
              @php
                $col = $tipo->tipo_proyecto;
              @endphp
              <td>{{ $totalSum[$col] }}</td>
            @endforeach
            <td>{{ $total }}</td>
          </tr>
        </tfoot>
        </table>
      @endif
    @endforeach
  </div>

  <script type="text/php">
    if (isset($pdf)) {
      $x = 772;
      $y = 571;
      $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
      $font = $fontMetrics->get_font("Helvetica", "Italic");
      $size = 8;
      $color = array(0,0,0);
      $word_space = 0.0;  //  default
      $char_space = 0.0;  //  default
      $angle = 0.0;   //  default
      $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
    }
  </script>
</body>

</html>
