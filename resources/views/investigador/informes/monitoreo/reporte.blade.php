@php
  use Carbon\Carbon;
   
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
      margin: 145px 40px 35px 40px;
    }

    .head-1 {
      position: fixed;
      top: -115px;
      left: 0px;
      height: 90px;
    }

    .head-1 img {
      margin-left: 120px;
      height: 85px;
    }

    .head-2 {
      position: fixed;
      top: -115px;
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

    .foot-1 {
      position: fixed;
      bottom: -15px;
      left: 0px;
      text-align: left;
      font-size: 11px;
      font-style: oblique;
    }

    .div {
      position: fixed;
      top: -15px;
      width: 100%;
      height: 0.5px;
      background: #000;
    }

    .titulo {
      font-size: 16px;
      text-align: center;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000;
      margin-bottom: 30px;
    }

    .table>thead th {
      font-size: 10px;
      border: 1.5px solid #000;
      padding: 5px 3px 6px 3px;
      font-weight: bold;
    }

    .table>tbody td {
      font-size: 11px;
      border: 1.5px solid #000;
      padding: 5px 3px 6px 3px;
    }

    .tableData {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .tableData>tbody td {
      font-size: 11px;
      padding: 5px 3px 6px 3px;
    }

    .desc {
      font-size: 11px;
    }

    .subtitulo {
      font-size: 14px;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="head-1">
    <img src="{{ public_path('head-pdf.jpg') }}" alt="Header">
  </div>
  <div class="head-2">
    <p class="rais">© RAIS</p>
    <p class="fecha">
      Fecha: {{ date('d/m/Y') }}<br>
      Hora: {{ date('H:i:s') }}
    </p>
    <br>
  </div>
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>
  <p class="titulo">
    <strong>
      Monitoreo
    </strong>
  </p>

  <p class="subtitulo">
    <strong>
      Estado: {{$datos->estado_meta}} {{ Carbon::parse($datos->updated_at)->format('d/m/Y') }}
    </strong>
  </p>

  <div class="cuerpo">

    <h5>I. Información del proyecto:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Título</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $datos->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Tipo de proyecto</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $datos->tipo_proyecto }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Código</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $datos->codigo_proyecto }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Estado del proyecto</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $datos->estado }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Facultad</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $datos->facultad }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Metas:</h5>

    <table class="table">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Cant. requerida</th>
          <th>Cant. completada</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($metas) > 0)
          @foreach ($metas as $item)
            <tr>
              <td>{{ $item->tipo_publicacion }}</td>
              <td>{{ $item->requerido }}</td>
              <td>{{ $item->completado }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="3" align="center">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>III. Publicaciones:</h5>

    <table class="table">
      <thead>
        <tr>
          <th>Título</th>
          <th>Tipo</th>
          <th>Año</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($publicaciones) > 0)
          @foreach ($publicaciones as $int)
            <tr>
              <td>{{ $int->titulo }}</td>
              <td>{{ $int->tipo_publicacion }}</td>
              <td>{{ $int->periodo }}</td>
              <td>{{ $int->estado }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="4" align="center">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>IV. Descripción del monitoreo:</h5>

    <div class="desc">
      @if (isset($datos->descripcion))
        {{ $datos->descripcion }}
      @endif
    </div>

  </div>

  <script type="text/php">
    if (isset($pdf)) {
      $x = 515;
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
