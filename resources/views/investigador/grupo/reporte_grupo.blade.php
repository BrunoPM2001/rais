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
      GRUPO DE INVESTIGACIÓN
      <br>
      Estado: {{ $grupo->estado }}
    </strong>
  </p>

  <div class="cuerpo">

    <h5>I. Datos del grupo:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Nombre</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->grupo_nombre }}</td>
        </tr>
        <tr>
          <td style="width: 24%;"><strong>Nombre corto</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 75%;">{{ $grupo->grupo_nombre_corto }}</td>
        </tr>
        <tr>
          <td style="width: 24%;"><strong>Teléfono</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 75%;">{{ $grupo->telefono }}</td>
        </tr>
        <tr>
          <td style="width: 24%;"><strong>Anexo</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 75%;">{{ $grupo->anexo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Oficina</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->oficina }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Dirección</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->direccion }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Direccion Web</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->web }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong> Correo institucional del coordinador</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->email }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Integrantes del grupo:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 15%;">Tipo de Investigador</th>
          <th style="width: 10%;">Dni</th>
          <th style="width: 40%;">Nombres</th>
          <th style="width: 15%;">Vínculo UNMSM</th>
          <th style="width: 20%;">Facultad</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($integrantes) == 0)
          <tr>
            <td colspan="5" align="center">
              No hay integrantes registrados
            </td>
          </tr>
        @endif
        @foreach ($integrantes as $item)
          <tr>
            <td>{{ $item->condicion }}</td>
            <td>{{ $item->doc_numero }}</td>
            <td>{{ $item->nombres }}</td>
            <td>{{ $item->tipo }}</td>
            <td>{{ $item->facultad }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h5>III. Presentación:</h5>

    <div class="desc">
      {{ $grupo->presentacion }}
    </div>

    <h5>IV. Objetivos:</h5>

    <div class="desc">
      {{ $grupo->objetivos }}
    </div>

    <h5>V. Servicios:</h5>

    <div class="desc">
      {{ $grupo->servicios }}
    </div>

    <h5>VI. Líneas de Investigación:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 30%;">Código</th>
          <th style="width: 70%;">Línea</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($lineas) == 0)
          <tr>
            <td colspan="2" align="center">
              No hay líneas registrados
            </td>
          </tr>
        @endif
        @foreach ($lineas as $item)
          <tr>
            <td>{{ $item->codigo }}</td>
            <td>{{ $item->nombre }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h5>VII. Ambientes físicos:</h5>

    <div class="desc">
      {{ $grupo->infraestructura_ambientes }}
    </div>

    <h5>VIII. Documentos sustentatorios, resoluciones decanales o constancias:</h5>

    <div class="desc">
      {{ $grupo->anexo }}
    </div>

    <h5>XI. Equipamiento de laboratorio/ gabinete:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 30%;">Código</th>
          <th style="width: 40%;">Laboratorio</th>
          <th style="width: 30%;">Responsable</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($laboratorios) == 0)
          <tr>
            <td colspan="3" align="center">
              No hay equipos registrados
            </td>
          </tr>
        @endif
        @foreach ($laboratorios as $item)
          <tr>
            <td>{{ $item->codigo }}</td>
            <td>{{ $item->laboratorio }}</td>
            <td>{{ $item->responsable }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

  </div>
</body>
