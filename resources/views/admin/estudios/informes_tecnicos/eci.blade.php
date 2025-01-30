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

    h6, h5 {
      margin-bottom: 5px;
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
      Programa de equipamiento científico para la investigación de la UNMSM
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
          <td style="width: 24%;" valign="top"><strong>Código</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->codigo_proyecto }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Facultad</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Grupo</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->grupo_nombre }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Coordinador</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->responsable }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Resolución </strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->resolucion_rectoral }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Contenido del informe:</h5>

    <h6>2.1 Resumen</h6>
    <div class="desc">
      {!! $detalles->resumen_ejecutivo !!}
    </div>

    <h6>2.2 Proceso de instalación</h6>
    <div class="desc">
      {!! $detalles->infinal1 !!}
    </div>

    <h6>2.3 Funcionamiento</h6>
    <div class="desc">
      {!! $detalles->infinal2 !!}
    </div>

    <h6>2.4 Gestión del uso</h6>
    <div class="desc">
      {!! $detalles->infinal3 !!}
    </div>

    <h5>III. Impacto:</h5>

    <h6>3.1 Aplicación práctica e impacto</h6>
    <div class="desc">
      {!! $detalles->infinal4 !!}
    </div>

    <h6>3.2 Uso</h6>
    <div class="desc">
      {!! $detalles->infinal5 !!}
    </div>

    
    <h5>IV. Documentos adjuntos:</h5>

    <table class="table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Documento de conformidad firmada por el coordinador del GI</td>
          <td>
            @if (isset($archivos['anexo1']))
              Sí
            @else
              No
            @endif
          </td>
        </tr>
        <tr>
          <td>Imágenes de equipo/gabinete instalado</td>
          <td>
            @if (isset($archivos['anexo2']))
              Sí
            @else
              No
            @endif
          </td>
        </tr>
        <tr>
          <td>Imágenes de equipos complementarios al equipo/gabinete instalado</td>
          <td>
            @if (isset($archivos['anexo3']))
              Sí
            @else
              No
            @endif
          </td>
        </tr>
        <tr>
          <td>Formato de control del uso del equipo, incluir uso compartido</td>
          <td>
            @if (isset($archivos['anexo4']))
              Sí
            @else
              No
            @endif
          </td>
        </tr>
      </tbody>
    </table>


    <h5>V. Dificultades encontradas:</h5>
    <div class="desc">
      {!! $detalles->infinal6 !!}
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
