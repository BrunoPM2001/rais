@php
  $tituloActual = '';
  $numTituloActual = 1;
  $firstEl = 0;
  $currentTipo = '';
  $firstTipo = false;
  //  Tipos de partida
  $subtotal = 0.0;
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
      margin-left: 120px;
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
      width: 754px;
      font-size: 16px;
      text-align: center;
    }

    .texto {
      font-size: 13px;
      margin: 20px 0;
    }

    .table1 {
      width: 100%;
      border-collapse: separate;
    }

    .table1>tbody {
      border-bottom: 1.5px solid #000;
    }

    .table1>thead {
      margin-top: -1px;
      font-size: 10px;
      border-top: 1.5px solid #000;
      border-bottom: 1.5px solid #000;
    }

    .table1>thead th {
      font-weight: normal;
    }

    .table1>tbody td {
      font-size: 10px;
      text-align: center;
      padding-top: 2px;
    }

    .table2 {
      width: 100%;
      border-collapse: separate;
      margin-bottom: 40px;
    }

    .table2>thead {
      margin-top: -1px;
      font-size: 10px;
      border-bottom: 1.5px solid #000;
    }

    .table2>thead th {
      text-align: left;
      font-weight: normal;
    }

    .table2>tbody td {
      font-size: 10px;
      padding-top: 2px;
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

    .titulo_proyecto {
      font-weight: bold;
      font-style: oblique;
    }

    .nom_grupo {
      background: #D7DFDF;
      font-size: 10px;
      padding: 2px;
      margin: 0 1px;
      border-top: 1.5px solid #000;
    }

    .nom_grupo>p {
      margin: 1px 5px;
    }

    .fac_grupo {
      font-size: 10px;
      margin: 2px;
      text-align: right;
    }
  </style>
</head>


<body>
  <div class="head-1">
    <img src="{{ public_path('head-pdf.jpg') }}" alt="Header">
    <p class="titulo">
      <strong>
        Presupuesto de proyectos - {{ $periodo }}
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
    @foreach ($lista as $item)
      @if ($tituloActual != $item->titulo)
        @if ($firstEl == 1)
          @if ($firstTipo)
            <tr>
              <td></td>
              <td></td>
              <td style="font-weight: bold; text-align: right;">Sub-total</td>
              <td style="text-align: right;">S./ {{ number_format($subtotal, 2) }}</td>
            </tr>
          @endif
          </tbody>
          </table>
        @endif
        @php
          $firstTipo = false;
          $firstEl = 1;
        @endphp
        <table class="table1">
          <thead>
            <tr>
              <th style="width: 5%;">Nro.</th>
              <th style="width: 85%;">Título del proyecto</th>
              <th style="width: 10%; text-align: right;">Monto total</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ $numTituloActual }}</td>
              <td class="titulo_proyecto">{{ $item->titulo }}</td>
              <td style="text-align: right;">S./ {{ number_format($montos[$numTituloActual - 1]->monto, 2) }}</td>
            </tr>
          </tbody>
        </table>
        <table class="table2">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Código</th>
              <th>Partida</th>
              <th style="text-align: right;">Monto</th>
            </tr>
          </thead>
          <tbody>
            @php
              $numTituloActual++;
            @endphp
      @endif
      @if ($currentTipo != $item->tipo)
        @if ($firstTipo)
          <tr>
            <td></td>
            <td></td>
            <td style="font-weight: bold; text-align: right;">Sub-total</td>
            <td style="text-align: right;">S./ {{ number_format($subtotal, 2) }}</td>
          </tr>
        @endif
        @php
          $firstTipo = true;
        @endphp
        <tr>
          <td>{{ ucfirst($item->tipo) }}</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        @php
          $subtotal = 0.0;
        @endphp
      @endif
      @php
        $subtotal += $item->monto;
      @endphp
      <tr>
        <td></td>
        <td>{{ $item->codigo }}</td>
        <td>{{ $item->partida }}</td>
        <td style="text-align: right;">S/. {{ number_format($item->monto, 2) }}</td>
      </tr>
      @php
        $currentTipo = $item->tipo;
        $tituloActual = $item->titulo;
      @endphp
      @if ($loop->last)
        <tr>
          <td></td>
          <td></td>
          <td style="font-weight: bold; text-align: right;">Sub-total</td>
          <td style="text-align: right;">S./ {{ number_format($subtotal, 2) }}</td>
        </tr>
        </tbody>
        </table>
      @endif
    @endforeach
  </div>

  <script type="text/php">
    if (isset($pdf)) {
      $x = 527;
      $y = 818;
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
