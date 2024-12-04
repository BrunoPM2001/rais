@php
  $monto_original = 0;
  $monto_modificado = 0;
  $monto_rendido = 0;
  $saldo_rendicion = 0;

  $exceso = 0;
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
      HOJA DE RESUMEN DE LA SUBVENCIÓN FINANCIERA<br>
      ESTADO: {{ $estado }}
    </strong>
  </p>
  <div class="cuerpo">

    <h5>I. Información del proyecto:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 12%;"><strong>Fecha</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->fecha_inscripcion }}</td>
        </tr>
        <tr>
          <td style="width: 12%;"><strong>Año</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->periodo }}</td>
        </tr>
        <tr>
          <td style="width: 12%;"><strong>Tipo</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->tipo_proyecto }}</td>
        </tr>
        <tr>
          <td style="width: 12%;"><strong>Código</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->codigo_proyecto }}</td>
        </tr>
        <tr>
          <td style="width: 12%;" valign="top"><strong>Título</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 87%;" valign="top">{{ $proyecto->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 12%;"><strong>Facultad</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 12%;" valign="top"><strong>Responsable</strong></td>
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

    <h5>II. Presupuesto:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 60%;">Descripción</th>
          <th style="width: 10%;">Presupuesto asignado (S/)</th>
          <th style="width: 10%;">Presupuesto modificado (S/)</th>
          <th style="width: 10%;">Rendido validado (S/)</th>
          <th style="width: 10%;">Saldo rendición (S/)</th>
        </tr>
      </thead>
      <tbody>
        @if (isset($presupuesto['Bienes']))
          <tr>
            <td class="row-left" style="font-weight: bold" colspan="5">Bienes</td>
          </tr>
          @foreach ($presupuesto['Bienes'] as $item)
            @php
              $monto_original += $item->monto_original;
              $monto_modificado += $item->monto_modificado;
              $monto_rendido += $item->monto_rendido;
              $saldo_rendicion += $item->saldo_rendicion;
              $exceso += $item->monto_excedido;
            @endphp
            <tr>
              <td class="row-left">{{ $item->partida }}</td>
              <td class="row-right">{{ $item->monto_original }}</td>
              <td class="row-right">{{ $item->monto_modificado }}</td>
              <td class="row-right">{{ $item->monto_rendido }}</td>
              <td class="row-right">{{ $item->saldo_rendicion }}</td>
            </tr>
          @endforeach
        @endif
        @if (isset($presupuesto['Servicios']))
          <tr>
            <td class="row-left" style="font-weight: bold" colspan="5">Servicios</td>
          </tr>
          @foreach ($presupuesto['Servicios'] as $item)
            @php
              $monto_original += $item->monto_original;
              $monto_modificado += $item->monto_modificado;
              $monto_rendido += $item->monto_rendido;
              $saldo_rendicion += $item->saldo_rendicion;
              $exceso += $item->monto_excedido;
            @endphp
            <tr>
              <td class="row-left">{{ $item->partida }}</td>
              <td class="row-right">{{ $item->monto_original }}</td>
              <td class="row-right">{{ $item->monto_modificado }}</td>
              <td class="row-right">{{ $item->monto_rendido }}</td>
              <td class="row-right">{{ $item->saldo_rendicion }}</td>
            </tr>
          @endforeach
        @endif
        <tr>
          <td class="row-right" style="font-weight: bold">Subvención Financiera</td>
          <td class="row-right" style="font-weight: bold">{{ number_format($monto_original, 2) }}</td>
          <td class="row-right" style="font-weight: bold">{{ number_format($monto_modificado, 2) }}</td>
          <td class="row-right" style="font-weight: bold">{{ number_format($monto_rendido, 2) }}</td>
          <td class="row-right" style="font-weight: bold">{{ number_format($saldo_rendicion, 2) }}</td>
        </tr>
      </tbody>
    </table>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 90%;"></th>
          <th style="width: 10%;">Monto (S/)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="row-right" style="font-weight: bold">Sub total</td>
          <td class="row-right">{{ number_format($monto_rendido, 2) }}</td>
        </tr>
        <tr>
          <td class="row-right" style="font-weight: bold">Excedente</td>
          <td class="row-right">{{ number_format($exceso, 2) }}</td>
        </tr>
        <tr>
          <td class="row-right" style="font-weight: bold">Total</td>
          <td class="row-right">{{ number_format($monto_rendido + $exceso, 2) }}</td>
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
