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
        PROYECTO DE CIENCIA, TECNOLOGÍA, INNOVACIÓN Y EMPRENDIMIENTO 2024
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
    {{-- Datos General --}}
    <h6>Información del GI</h6>
    <p><b>Grupo de investigación: </b> {{ $proyecto->grupo_nombre }}</p>
    <p><b>Facultad: </b>{{ $proyecto->facultad_nombre }}</p>
    <p><b>Área académica: </b>{{ $proyecto->area_nombre }}</p>

    <h6>Proyecto</h6>
    <p><b>Código de proyecto: </b>{{ $proyecto->codigo_proyecto }}</p>
    <p><b>Linea de Investigación: </b>{{ $proyecto->linea_nombre }}</p>
    <p><b>Objetivo de Desarrollo Sostenible(ODS): </b>{{ $proyecto->objetivo }}</p>
    <p><b>línea OCDE: </b>{{ $proyecto->linea }}</p>
    <p><b>Localización: </b>{{ $proyecto->localizacion }}</p>

    <!--  Descripcion del Proyecto  -->
    @foreach ($descripcion as $des)
      @switch($des->codigo)
        @case('resumen_ejecutivo')
          <h6>Resumen Ejecutivo</h6>
        @break

        @case('antecedentes')
          <h6>Antecedentes</h6>
        @break

        @case('justificacion')
          <h6>Justificación</h6>
        @break

        @case('contribucion_impacto')
          <h6>Contribucion e impacto</h6>
        @break

        @case('hipotesis')
          <h6>Hipótesis</h6>
        @break

        @case('objetivos')
          <h6>Objetivos</h6>
        @break

        @case('metodologia_trabajo')
          <h6>Metodología</h6>
        @break

        @case('referencias_bibliograficas')
          <h6>Referencias Bibliográficas</h6>
        @break

        @default
      @endswitch
      <p>{{ $des->detalle }}</p>
    @endforeach

    <!--  Actividades  -->
    <?php $i = 1; ?>
    <h6>Calendario de actividades</h6>
    @foreach ($actividades as $act)
      <table class="table1">
        <thead>
          <tr>
            <th>Nro.</th>
            <th>Actividad</th>
            <th>Fecha inicial</th>
            <th>Fecha final</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ $i }}</td>
            <td>{{ $act->actividad }}</td>
            <td>{{ $act->fecha_inicio }}</td>
            <td>{{ $act->fecha_fin }}</td>
          </tr>
        </tbody>
      </table>
      <?php $i++; ?>
    @endforeach

    <!--  Presupuesto  -->
    <h6>Presupuesto</h6>
    @foreach ($presupuesto as $pres)
      <table class="table1">
        <thead>
          <tr>
            <th>Nro</th>
            <th>Partida</th>
            <th>Monto S/.</th>
            <th>Tipo</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ $i }}</td>
            <td>{{ $pres->partida }}</td>
            <td>{{ $pres->monto }}</td>
            <td>{{ $pres->tipo }}</td>
          </tr>
        </tbody>
      </table>
      <?php $i++; ?>
    @endforeach

    <!--  Integrantes  -->
    <h6>Integrantes</h6>
    @foreach ($integrantes as $int)
      <table class="table1">
        <thead>
          <tr>
            <th>Nro</th>
            <th>Condición</th>
            <th>Apellidos y Nombres </th>
            <th>Tipo</th>
            <th>Facultad</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{ $i }}</td>
            <td>{{ $int->condicion }}</td>
            <td>{{ $int->integrante }}</td>
            <td>{{ $int->tipo }}</td>
            <td>{{ $int->facultad }}</td>
          </tr>
        </tbody>
      </table>
      <?php $i++; ?>
    @endforeach

    {{-- Condiciones --}}
    <h6>Condiciones</h6>
    <p>Los docentes y/o estudiantes deben indicar, en cada publicación o forma de divulgación (tesis, artículos,
      libros, resúmenes de trabajos presentados en congresos, páginas de
      internet y cualquier otra publicación) que resulten del apoyo de la UNMSM, el siguiente párrafo:
    </p>
    <p>Para los artículos escritos en español:</p>
    <p> Esta investigación fue financiada por la Universidad Nacional Mayor de San Marcos – RR N° aabb-cc con código
      de proyecto dfgh.
    </p>
    <p> Para los artículos escritos en algún idioma extranjero, indicar el apoyo de la UNMSM en inglés:</p>
    <p>This research was supported by the Universidad Nacional Mayor de San Marcos – RR N° aabb-cc and project
      number dfgh.
    </p>

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
