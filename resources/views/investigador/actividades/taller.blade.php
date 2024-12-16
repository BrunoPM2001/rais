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
          <td style="width: 75%;" valign="top">{{ $proyecto->clase }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Resolución de designación oficial</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->anexo }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Comité organizador del taller:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 14%;">Condición</th>
          <th style="width: 14%;">Apellido paterno</th>
          <th style="width: 14%;">Apellido materno</th>
          <th style="width: 14%;">Nombres</th>
          <th style="width: 14%;">DNI</th>
          <th style="width: 14%;">Código</th>
          <th style="width: 16%;">Correo</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($miembros) == 0)
          <tr>
            <td colspan="9" align="center">
              No hay miembros en el comité
            </td>
          </tr>
        @endif
        @foreach ($miembros as $item)
          <tr>
            <td>{{ $item->condicion }}</td>
            <td>{{ $item->apellido1 }}</td>
            <td>{{ $item->apellido2 }}</td>
            <td>{{ $item->nombres }}</td>
            <td>{{ $item->doc_numero }}</td>
            <td>{{ $item->codigo }}</td>
            <td>{{ $item->email3 }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h5>III. Plan de trabajo:</h5>

    <h6>Justificación</h6>
    <div class="desc">
      {!! $detalles['justificacion'] !!}
    </div>

    <h6>Objetivos</h6>
    <div class="desc">
      {!! $detalles['objetivo'] !!}
    </div>

    <h6>Metas específicas</h6>
    <div class="desc">
      {!! $detalles['metas'] !!}
    </div>

    <h5>IV. Programa del taller:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 5%;">N°</th>
          <th style="width: 55%;">Actividad</th>
          <th style="width: 15%;">Fecha inicial</th>
          <th style="width: 15%;">Fecha final</th>
          <th style="width: 10%;">Horas</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($actividades) == 0)
          <tr>
            <td colspan="4" align="center">
              No hay actividades registradas
            </td>
          </tr>
        @endif
        @foreach ($actividades as $item)
          <tr>
            <td style="text-align: center">{{ $loop->iteration }}</td>
            <td>{{ $item->actividad }}</td>
            <td>{{ $item->fecha_inicio }}</td>
            <td>{{ $item->fecha_fin }}</td>
            <td>{{ $item->duracion }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h5>V. Financiamiento:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 10%;">Código</th>
          <th style="width: 25%;">Partida</th>
          <th style="width: 20%;">Tipo</th>
          <th style="width: 20%;">Monto</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($presupuesto as $item)
          <tr>
            <td>{{ $item->codigo }}</td>
            <td>{{ $item->partida }}</td>
            <td>{{ $item->tipo }}</td>
            <td>{{ $item->monto }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Cofinanciamiento de Facultad</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">S/ {{ $detalles['facultad_monto'] }}</td>
        </tr>
        <tr>
          <td style="width: 24%;"><strong>Subvención económica VRIP</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 75%;">S/ 2575</td>
        </tr>
        <tr>
          <td style="width: 24%;"><strong>Documento RD de cofinanciamiento</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 75%;">{{ $proyecto->rd }}</td>
        </tr>
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
