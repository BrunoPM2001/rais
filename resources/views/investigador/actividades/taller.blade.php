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

    .subtitulo {
      font-size: 14px;
      text-align: center;
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
      TALLERES DE INVESTIGACIÓN Y POSGRADO<br>
      RESUMEN EJECUTIVO
    </strong>
  </p>

  <p class="subtitulo">
    <strong>
      Estado: {{ $proyecto->estado }}
      {{ Carbon::parse($proyecto->updated_at)->format('d/m/Y') }}
    </strong>
  </p>

  <div class="cuerpo">

    <h5>I. Información general del taller:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Título</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;"><strong>Responsable</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 75%;">{{ $proyecto->responsable }}</td>
        </tr>
        <tr>
          <td style="width: 24%;"><strong>Dni</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 75%;">{{ $proyecto->doc_numero }}</td>
        </tr>
        <tr>
          <td style="width: 24%;"><strong>Correo electrónico</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 75%;">{{ $proyecto->email3 }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Facultad</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Cód. Docente</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->codigo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Categoría y clase</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->categoria }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Resolución de designación oficial</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->documento }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Comité organizador del taller:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 11%;">Condición</th>
          <th style="width: 11%;">Cargo</th>
          <th style="width: 11%;">Apellido paterno</th>
          <th style="width: 11%;">Apellido materno</th>
          <th style="width: 11%;">Nombres</th>
          <th style="width: 11%;">DNI</th>
          <th style="width: 11%;">Código</th>
          <th style="width: 12%;">Correo</th>
          <th style="width: 11%;">Facultad</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($comite) == 0)
          <tr>
            <td colspan="9" align="center">
              No hay miembros en el comité
            </td>
          </tr>
        @endif
        @foreach ($comite as $item)
          <tr>
            <td style="text-align: center">{{ $loop->iteration }}</td>
            <td>{{ $item->actividad }}</td>
            <td>{{ $item->fecha_inicio }}</td>
            <td>{{ $item->fecha_fin }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="desc">
      {!! $detalles['resumen_ejecutivo'] !!}
    </div>

    <h5>IV. Palabras clave:</h5>

    <div class="desc">
      {{ $proyecto->palabras_clave }}
    </div>

    <h5>V. Antecedentes:</h5>

    <div class="desc">
      {!! $detalles['antecedentes'] !!}
    </div>

    <h5>VI. Justificación:</h5>

    <div class="desc">
      {!! $detalles['justificacion'] !!}
    </div>

    <h5>VII. Contribución e impacto:</h5>

    <div class="desc">
      {!! $detalles['contribucion_impacto'] !!}
    </div>

    <h5>VIII. Hipótesis:</h5>

    <div class="desc">
      {!! $detalles['hipotesis'] !!}
    </div>

    <h5>IX. Objetivos:</h5>

    <div class="desc">
      {!! $detalles['objetivos'] !!}
    </div>

    <h5>X. Metodología de trabajo:</h5>

    <div class="desc">
      {!! $detalles['metodologia_trabajo'] !!}
    </div>

    <h5>XI. Referencias bibliográficas:</h5>

    <div class="desc">
      {!! $detalles['referencias_bibliograficas'] !!}
    </div>

    <h5>XII. Calendario:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 5%;">N°</th>
          <th style="width: 65%;">Actividad</th>
          <th style="width: 15%;">Fecha inicial</th>
          <th style="width: 15%;">Fecha final</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($calendario) == 0)
          <tr>
            <td colspan="4" align="center">
              No hay actividades registradas
            </td>
          </tr>
        @endif
        @foreach ($calendario as $item)
          <tr>
            <td style="text-align: center">{{ $loop->iteration }}</td>
            <td>{{ $item->actividad }}</td>
            <td>{{ $item->fecha_inicio }}</td>
            <td>{{ $item->fecha_fin }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h5>XIII. Integrantes:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 10%;">Condición</th>
          <th style="width: 25%;">Apellidos y nombres</th>
          <th style="width: 20%;">Tipo</th>
          <th style="width: 20%;">Tipo de tesis</th>
          <th style="width: 25%;">Título de la tesis</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($integrantes as $item)
          <tr>
            <td>{{ $item->condicion }}</td>
            <td>{{ $item->nombres }}</td>
            <td>{{ $item->tipo }}</td>
            <td>{{ $item->tipo_tesis }}</td>
            <td>{{ $item->titulo_tesis }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h5>XIV. Condiciones:</h5>

    <div class="desc">
      Los docentes y/o estudiantes deben indicar, en cada publicación o forma de divulgación (tesis, artículos, libros,
      resúmenes de trabajos presentados en congresos, páginas de
      internet y cualquier otra publicación) que resulten del apoyo de la UNMSM, el siguiente párrafo:
      <br>
      <br>
      <i>
        Para los artículos escritos en español:
      </i><br>
      Esta investigación fue financiada por la Universidad Nacional Mayor de San Marcos – RR N° aabb-cc con código de
      proyecto dfgh.
      <br>
      <br>
      <i>
        Para los artículos escritos en algún idioma extranjero, indicar el apoyo de la UNMSM en inglés:
      </i><br>
      This research was supported by the Universidad Nacional Mayor de San Marcos – RR N° aabb-cc and project number
      dfgh.
    </div>
  </div>
</body>
