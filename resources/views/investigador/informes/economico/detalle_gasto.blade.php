@php
  $total_bienes = 0;
  $total_servicios = 0;
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
      DETALLE DE GASTO DE PROYECTOS DE INVESTIGACIÓN CON FINANCIAMIENTO PARA GI
    </strong>
  </p>
  <div class="cuerpo">

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 12%;" valign="top"><strong>Grupo de investigación</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 87%;" valign="top">{{ $proyecto->grupo_nombre }}</td>
        </tr>
        <tr>
          <td style="width: 12%;"><strong>Facultad GI</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 12%;" valign="top"><strong>Título del proyecto</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 87%;" valign="top">{{ $proyecto->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 12%;"><strong>Responsable</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->responsable }}</td>
        </tr>
        <tr>
          <td style="width: 12%;"><strong>Correo</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->email3 }}</td>
        </tr>
        <tr>
          <td style="width: 12%;"><strong>Teléfono</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->telefono_movil }}</td>
        </tr>
      </tbody>
    </table>

    <!--  Bienes  -->

    <table class="table">
      <thead>
        <tr>
          <th colspan="6">Bienes</th>
        </tr>
        <tr>
          <th colspan="3" style="width: 40%">Comprobantes</th>
          <th rowspan="2" style="width: 10%">Partida específica</th>
          <th rowspan="2" style="width: 40%">Descripción de Partida</th>
          <th rowspan="2" style="width: 10%">Monto (S/)</th>
        </tr>
        <tr>
          <th>Fecha</th>
          <th>Tipo</th>
          <th>N°</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($bienes) == 0)
          <tr>
            <td colspan="6" align="center">
              No hay comprobantes registrados
            </td>
          </tr>
        @endif
        @foreach ($bienes as $item)
          <tr>
            <td>{{ $item->fecha }}</td>
            <td>{{ $item->tipo }}</td>
            <td>{{ $item->numero }}</td>
            <td>{{ $item->codigo }}</td>
            <td>{{ $item->partida }}</td>
            <td style="text-align: right;">S/ {{ number_format($item->total, 2) }}</td>
          </tr>
          @php
            $total_bienes += $item->total;
          @endphp
        @endforeach
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: right;"><strong>Sub Total</strong></td>
            <td style="text-align: right;"><strong>S/ {{ number_format($total_bienes, 2) }}</strong></td>
          </tr>
      </tbody>
    </table>

    <!--  Servicios  -->

    <table class="table">
      <thead>
        <tr>
          <th colspan="6">Servicios</th>
        </tr>
        <tr>
          <th colspan="3" style="width: 40%">Comprobantes</th>
          <th rowspan="2" style="width: 10%">Partida específica</th>
          <th rowspan="2" style="width: 40%">Descripción de Partida</th>
          <th rowspan="2" style="width: 10%">Monto (S/)</th>
        </tr>
        <tr>
          <th>Fecha</th>
          <th>Tipo</th>
          <th>N°</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($servicios) == 0)
          <tr>
            <td colspan="6" align="center">
              No hay comprobantes registrados
            </td>
          </tr>
        @endif
        @foreach ($servicios as $item)
          <tr>
            <td>{{ $item->fecha }}</td>
            <td>{{ $item->tipo }}</td>
            <td>{{ $item->numero }}</td>
            <td>{{ $item->codigo }}</td>
            <td>{{ $item->partida }}</td>
            <td style="text-align: right;">S/ {{ number_format($item->total, 2) }}</td>
          </tr>
          @php
            $total_servicios += $item->total;
          @endphp
        @endforeach
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="text-align: right;"><strong>Sub Total</strong></td>
            <td style="text-align: right;"><strong>S/ {{ number_format($total_servicios, 2) }}</strong></td>
          </tr>
      </tbody>
    </table>

    <table class="table">
      <thead>
        <tr>
          <th></th>
          <th style="text-align: right;">Sub Total Bienes</th>
          <th style="text-align: right;">Sub Total Servicios</th>
          <th style="text-align: right;">Total</th>
        </tr>
      </thead>
      <tbody>
          <tr>
            <td>Resumen S/ :</td>
            <td style="text-align: right;">{{ number_format($total_bienes, 2) }}</td>
            <td style="text-align: right;">{{ number_format($total_servicios, 2) }}</td>
            <td style="text-align: right;">{{ number_format($total_bienes + $total_servicios, 2) }}</td>
          </tr>
      </tbody>
    </table>

    <p style="text-align: justify">
      Yo <strong>{{ $proyecto->responsable }}</strong> declaro bajo juramento que lo consignado en el presente reporte
      y los documentos adjuntos por
      concepto de rendición económica, es el resultado de la información registrada en el sistema RAIS - Registro de
      comprobantes; dando fe que dichos
      gastos corresponden al desarrollo de la actividad de investigación citado lineas arriba, en concordancia al
      presupuesto asignado para tal fin.
    </p>

    <table class="firmas">
      <thead>
        <tr>
          <th style="width: 45%; border-top: 1px solid #000; padding-top: 20px;">Responsable</th>
          <th style="width: 10%"></th>
          <th style="width: 45%; border-top: 1px solid #000; padding-top: 20px;">V°B° de conocimiento Vicedecanato
            de <br>
            investigación y
            posgrado.</th>
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
