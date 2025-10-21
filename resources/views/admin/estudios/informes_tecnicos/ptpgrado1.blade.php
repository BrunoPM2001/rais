@php
  use Carbon\Carbon;
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

    .subtitulo {
      font-size: 14px;
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
      font-size: 10px;
    }

    .obs {
      background-color: #ff9a9a;
      border-radius: 2px;
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
      padding: 2px 4px;
    }

    .obs>tbody td {
      font-size: 11px;
      padding: 5px 3px 6px 3px;
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
  <div class="foot-1">RAIS - UNMSM - {{ $proyecto->codigo_proyecto }} - {{ $detalles->id }}</div>
  <p class="titulo">
    <strong>
      Programa de promoción de tesis de pregrado {{ $proyecto->periodo }}
    </strong>
  </p>

  <p class="subtitulo">
    <strong>
      {{ $informe }}<br>
      Estado:
      @switch($detalles->estado)
        @case(1)
          Aprobado
        @break

        @case(2)
          Presentado
        @break

        @case(3)
          Observado
        @break

        @default
          Sin estado
      @endswitch
      {{ Carbon::parse($detalles->fecha_estado)->format('d/m/Y') }}
    </strong>
  </p>

  <div class="cuerpo">

    @if ($detalles->estado == 3)
      <table class="obs">
        <tbody>
          <tr>
            <td style="width: 12%;" valign="top"><strong>Observaciones</strong></td>
            <td style="width: 1%;" valign="top">:</td>
            <td style="width: 87%;" valign="top">{{ $detalles->observaciones }}</td>
          </tr>
        </tbody>
      </table>
    @endif

    <h5>I. Datos generales:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Título</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Tipo de tesis</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->tipo_proyecto }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Código</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->codigo_proyecto }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Resolución </strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->resolucion_rectoral }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Año</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->periodo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Asesor</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $miembros['Asesor'] }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Tesista</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $miembros['Tesista'] }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Facultad</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->facultad }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Contenido del informe:</h5>

    <h6>2.1. Descripción de actividades realizadas:</h6>
    <div class="desc">
      {!! $detalles->infinal1 !!}
    </div>

    <h6>2.2. Porcentaje de avance:</h6>
    <div class="desc">
      {!! $detalles->infinal7 !!}
    </div>

    <h6>2.3 Evaluación global de ejecución técnica académica:</h6>
    <div class="desc">
      {!! $detalles->infinal3 !!}
    </div>

    <h6>2.4 Medios probatorios:</h6>
    <div class="desc">
      @if (isset($archivos['informe-PTPGRADO-INFORME-AVANCE']))
        Sí
      @else
        No
      @endif
    </div>

    <h6>2.5 Problemas identificados:</h6>
    <div class="desc">
      {!! $detalles->infinal2 !!}
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
