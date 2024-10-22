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
      PROYECTO DE INVESTIGACIÓN – SIN FINANCIAMIENTO
    </strong>
  </p>

  <div class="cuerpo">

    <h5>I. Información general del proyecto:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Título</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Grupo</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->grupo_nombre }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Área académica</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->area }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Facultad</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Línea de investigación</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->linea }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Tipo de investigación</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->tipo_investigacion }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Localización</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->localizacion }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Línea OCDE </strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->ocde }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Descripción del Proyecto:</h5>

    <h6>Resumen ejecutivo</h6>
    <div class="desc">
      {!! $detalles['resumen_ejecutivo'] !!}
    </div>

    <h6>Palabras clave</h6>
    <div class="desc">
      {{ $proyecto->palabras_clave }}
    </div>

    <h6>Antecedentes</h6>
    <div class="desc">
      {!! $detalles['antecedentes'] !!}
    </div>

    <h6>Objetivos generales</h6>
    <div class="desc">
      {!! $detalles['objetivos_generales'] !!}
    </div>

    <h6>Objetivos específicos</h6>
    <div class="desc">
      {!! $detalles['objetivos_especificos'] !!}
    </div>

    <h6>Justificación</h6>
    <div class="desc">
      {!! $detalles['justificacion'] !!}
    </div>

    <h6>Hipótesis</h6>
    <div class="desc">
      {!! $detalles['hipotesis'] !!}
    </div>

    <h6>Metodología de trabajo</h6>
    <div class="desc">
      {!! $detalles['metodologia_trabajo'] !!}
    </div>

    <h6>Referencias bibliográficas</h6>
    <div class="desc">
      {!! $detalles['referencias_bibliograficas'] !!}
    </div>

    <h6>Resultados esperados</h6>
    <div class="desc">
      {!! $detalles['resumen_esperado'] !!}
    </div>

    <h5>III. Datos del responsable:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Código</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->codigo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Dependencia</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->dependencia }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Facultad</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>CTI-Vitae</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->cti_vitae }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Código ORCID</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->codigo_orcid }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Scopus ID</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->scopus_id }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Google Scholar</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->google_scholar }}</td>
        </tr>
      </tbody>
    </table>

    <h5>IV. Integrantes:</h5>

    <table class="table">
      <thead>
        <tr>
          <th>Condición</th>
          <th>Apellidos y Nombres </th>
          <th>Tipo</th>
          <th>Tipo de tesis</th>
          <th>Título de tesis</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($integrantes as $int)
          <tr>
            <td>{{ $int->condicion }}</td>
            <td>{{ $int->integrante }}</td>
            <td>{{ $int->tipo }}</td>
            <td>{{ $int->tipo_tesis }}</td>
            <td>{{ $int->titulo_tesis }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h5>V. Calendario de actividades:</h5>
    <table class="table">
      <thead>
        <tr>
          <th>Nro.</th>
          <th>Actividad</th>
          <th>Fecha inicial</th>
          <th>Fecha final</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($actividades as $act)
          <tr>
            <td align="center">{{ $loop->iteration }}</td>
            <td>{{ $act->actividad }}</td>
            <td>{{ $act->fecha_inicio }}</td>
            <td>{{ $act->fecha_fin }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

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
