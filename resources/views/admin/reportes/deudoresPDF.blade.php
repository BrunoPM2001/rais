@php
  $totalRegistros = count($lista);
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Reporte de Deudores</title>
  <style>
    @page {
      margin-top: 190px;
      margin-bottom: 95px;
      size: A4 landscape;
    }

    * {
      font-family: Arial, sans-serif;
    }

    .header-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 10px;
    }

    .header-table td {
      vertical-align: middle;
    }

    .header-left {
      width: 14%;
      text-align: left;
      font-size: 10px;
    }

    header {
      position: fixed;
      top: -160px;
      left: 0;
      right: 0;
    }

    .content {
      margin-top: 2px;
    }

    .header-center {
      width: 72%;
      text-align: center;
    }

    .header-right {
      width: 14%;
      text-align: right;
      font-size: 10px;
    }

    .header-center img {
      max-width: 100%;
      max-height: 100px;
      object-fit: contain;
    }

    .cuerpo-table {
      width: 100%;
      text-align: center;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 5px;
      margin-top: 0px;
    }

    .title {
      font-size: 16px;
      text-align: center;
      margin-bottom: 0px;
      color: #0a0a84;
    }

    .table-texto {
      width: 100%;
      font-size: 12px;
      text-align: justify;
    }

    .detalle-comentario {
      font-size: 12px;
      margin-bottom: 5px;
      line-height: 1.3;
      text-align: right;
    }

    .table-content {
      width: 100%;
      font-size: 10px;
      margin-bottom: 10px;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .table-content thead th {
      border-top: 1px solid black;
      border-bottom: 1px solid black;
      padding: 6px;
      text-align: center;
    }

    .table-content tbody td {
      padding: 5px;
      vertical-align: top;
      text-align: left;
    }

    .table-content tbody tr:last-child td {
      border-bottom: 1px solid black;
    }

    .foot-1 {
      position: fixed;
      bottom: -60px;
      width: 100%;
    }

    .resumen {
      font-size: 12px;
      margin-top: 8px;
      text-align: right;
    }
  </style>
</head>

<body>
  @php
    $agrupado = collect($lista)->groupBy('facultad_investigador');
  @endphp
  <header>
    <table class="header-table">
      <tr>
        <td class="header-left">
          <span>Fecha: {{ date('d/m/Y') }}</span><br>
          <span>Hora: {{ date('H:i:s') }}</span>
        </td>
        <td class="header-center">
          <img src="{{ public_path('head-pdf.jpg') }}" alt="Header">
        </td>
        <td class="header-right">
          <span>© RAIS</span><br>
          <span>{{ $username }}</span>
        </td>
      </tr>
    </table>

    <div style="border-top: 2px solid black; margin: 5px 0;"></div>
    <table class="cuerpo-table">
      <tr class="title">
        <td>
          <b>Listado de docentes que adeudan Informes académicos y/o económicos de actividades de Investigación</b>
        </td>
      </tr>
    </table>
    <div style="border-top: 1px solid black; margin: 0px 0;"></div>
  </header>

  <div class="foot-1">
    <div style="border-top: 2px solid black; margin: 1px 0;width:40%;"></div>
    <table style="width: 100%; font-size: 9px; font-style: italic; table-layout: fixed;">
      <tr>
        <td style="width: 90%; text-align: justify; line-height: 1.3;">
          RAIS - Registro de actividades de investigación de SAN MARCOS
        </td>
        <td style="width: 10%; text-align: center;">
          <span style="font-size: 10px;">
            Página <span class="page"></span><span class="topage"></span>
          </span>
        </td>
      </tr>
    </table>
  </div>
  @foreach($agrupado as $facultadNombre => $items)

  <table class="table-content">
    <thead>
      <tr>
        <th colspan="8" style="text-align:left; font-size:12px; padding:6px;">
          Facultad: {{ $facultadNombre ?? 'General' }}
        </th>
      </tr>
      <tr>
        <th style="width: 2%;">Nro.</th>
        <th style="width: 5%;">Cód. Doc.</th>
        <th style="width: 27%;">Apellidos y Nombres</th>
        <th style="width: 9%; text-align: left;">Tipo</th>
        <th style="width: 8%;">Cód. Estudio</th>
        <th style="width: 11%;">Condición</th>
        <th style="width: 12%;">Detalle de Deuda</th>
        <th style="width: 5%;">Año deuda</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $index => $item)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td style="text-align:center;">{{ $item->codigo }}</td>
          <td>{{ $item->nombres }}</td>
          <td>{{ $item->tipo_proyecto }}</td>
          <td>{{ $item->codigo_proyecto }}</td>
          <td>{{ $item->condicion }}</td>
          <td>{{ $item->categoria }}</td>
          <td style="text-align:center;">{{ $item->periodo }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" style="text-align:center; padding: 12px;">
            No se encontraron registros.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="resumen">
    <strong>Total de deudores: {{ count($items) }}</strong>
  </div>
  @if(!$loop->last)
    <div style="page-break-after: always;"></div>
  @endif
@endforeach

  <script type="text/php">
    if (isset($pdf)) {
      $x = 782;
      $y = 558;
      $text = "{PAGE_NUM} de {PAGE_COUNT}";
      $font = $fontMetrics->get_font("Helvetica", "Italic");
      $size = 8;
      $color = array(0,0,0);
      $word_space = 0.0;
      $char_space = 0.0;
      $angle = 0.0;
      $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
    }
  </script>
</body>

</html>