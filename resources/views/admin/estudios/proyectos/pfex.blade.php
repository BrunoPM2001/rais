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
      PROYECTO FEX {{ $proyecto->periodo }}
    </strong>
  </p>

  <p class="subtitulo">
    <strong>
      Estado: {{ $proyecto->estado }} {{ Carbon::parse($proyecto->updated_at)->format('d/m/Y') }}
    </strong>
  </p>

  <div class="cuerpo">

    <h5>I. Información del proyecto:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Código</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->codigo_proyecto }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Título</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Línea de investigación</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->linea_investigacion }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Línea OCDE</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->linea_ocde }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Moneda</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $extras['moneda_tipo'] }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Monto total del presupuesto</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->monto }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Aporte monetario UNMSM</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->aporte_unmsm }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Aporte no monetario UNMSM</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->aporte_no_unmsm }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Aporte fuente financiadora</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->financiamiento_fuente_externa }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Aporte entidad asociado y/o colaboradora</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->entidad_asociada }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Fuente financiadora</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">
            @if ($extras['fuente_financiadora'] == 'OTROS')
              {{ $extras['otra_fuente'] }}
            @else
              {{ $extras['fuente_financiadora'] }}
            @endif
          </td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>País</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->pais }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Web fuente financiadora</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $extras['web_fuente'] }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>La UNMSM participa como</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $extras['participacion_unmsm'] }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Resolución rectoral</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->resolucion_rectoral }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Palabras clave</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->palabras_clave }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Fecha de inicio</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->fecha_inicio }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Fecha de cierre</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->fecha_fin }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Duración del proyecto</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">
            @if (isset($extras['duracion_dia']))
              {{ $extras['duracion_dia'] }}
            @endif
            día(s) -
            @if (isset($extras['duracion_mes']))
              {{ $extras['duracion_mes'] }}
            @endif
            mes(es) -
            @if (isset($extras['duracion_mes']))
              {{ $extras['duracion_anio'] }}
            @endif
            año(s)
          </td>
        </tr>
      </tbody>
    </table>

    <h5>II. Documentos:</h5>

    <table class="table">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Título / Nombres</th>
          <th>Comentario</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($documentos) > 0)
          @foreach ($documentos as $item)
            <tr>
              <td>{{ $item->doc_tipo }}</td>
              <td>{{ $item->nombre }}</td>
              <td>{{ $item->comentario }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="3" align="center">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>III. Integrantes del proyecto:</h5>

    <table class="table">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Nombres</th>
          <th>N° de documento</th>
          <th>Representa a la UNMSM</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($integrantes) > 0)
          @foreach ($integrantes as $int)
            <tr>
              <td>{{ $int->tipo }}</td>
              <td>{{ $int->nombre }}</td>
              <td>{{ $int->doc_numero }}</td>
              <td>{{ $int->representa }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="4" align="center">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>IV. Resumen ejecutivo:</h5>

    <div class="desc">
      @if (isset($extras['resumen']))
        {!! $extras['resumen'] !!}
      @endif
    </div>

    <h5>V. Objetivos:</h5>

    <div class="desc">
      @if (isset($extras['objetivos']))
        {!! $extras['objetivos'] !!}
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
