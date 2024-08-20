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
      margin: 145px 40px 20px 40px;
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
      bottom: 0px;
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

    .tableData {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .tableData>tbody td {
      font-size: 11px;
      padding: 5px 3px 6px 3px;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000;
      margin-bottom: 30px;
    }

    .firmas {
      width: 100%;
      border-collapse: collapse;
      font-size: 10px;
      margin-top: 120px;
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

    .row-left {
      text-align: left;
    }

    .row-right {
      text-align: right;
      padding-right: 10px;
    }

    .cuerpo>p {
      font-size: 11px;
    }
    .desc {
      font-size: 11px;
    }
    .desc p span {
      font-size: 11px !important;
    }
    .desc img {
      width: 100% !important; 
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
      Proyectos de investigación para grupos de investigación {{ $proyecto->periodo }}<br><br>
      {{ $proyecto->informe }}<br>
      Estado: {{ $proyecto->estado }} {{ Carbon::parse($proyecto->updated_at)->format('d/m/Y') }}
    </strong>
  </p>
  <div class="cuerpo">

    <h5>I. Datos generales</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 16%;"><strong>1.1 Título</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 83%;">{{ $proyecto->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 16%;"><strong>1.2 Código</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 83%;">{{ $proyecto->codigo_proyecto }}</td>
        </tr>
        <tr>
          <td style="width: 16%;"><strong>1.3 Facultad</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 83%;">{{ $proyecto->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 16%;"><strong>1.4 Grupo</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 83%;">{{ $proyecto->grupo_nombre }}</td>
        </tr>
        <tr>
          <td style="width: 16%;" valign="top"><strong>1.5 Coordinador</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 83%;" valign="top">{{ $proyecto->coordinador }}</td>
        </tr>
        <tr>
          <td style="width: 16%;"><strong>1.6 Resolución</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 83%;">{{ $proyecto->resolucion_rectoral }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Contenido del informe</h5>

    <h5>2.1 Resumen</h5>
    <div class="desc">
      {!! $proyecto->resumen_ejecutivo !!}
    </div>

    <h5>2.2 Procesos de instalación</h5>
    <div class="desc">
      {!! $proyecto->infinal1 !!}
    </div>

    <h5>2.3 Funcionamiento</h5>
    <div class="desc">
      {!! $proyecto->infinal2 !!}
    </div>

    <h5>2.4 Gestión del uso</h5>
    <div class="desc">
      {!! $proyecto->infinal3 !!}
    </div>

    <h5>III. Impacto</h5>
    <h5>3.1 Aplicación práctica e impacto</h5>
    <div class="desc">
      {!! $proyecto->infinal4 !!}
    </div>

    <h5>3.2 Uso</h5>
    <div class="desc">
      {!! $proyecto->infinal5 !!}
    </div>

    <h5>IV. Dificultades encontradas</h5>
    <div class="desc">
      {!! $proyecto->infinal6 !!}
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
