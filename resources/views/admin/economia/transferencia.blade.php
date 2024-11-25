@php
  $currentTipo = '';
  $subtotal = 0;
  $puntajetotal = 0.0;
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
      margin: 145px 20px 20px 20px;
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

    .head-2 .user {
      font-size: 10px;
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

    .table>thead th {
      font-size: 10px;
      border: 1.5px solid #000;
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
    <p class="user">
      administrador
    </p>
  </div>
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <p class="titulo"><strong>Reporte de transferencia</strong></p>
  <div class="cuerpo">

    <h5>I. Información del proyecto:</h5>

    <table class="tableData">
      <tbody>
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
          <td style="width: 87%;">{{ $proyecto->facultad ?? "" }}</td>
        </tr>
        <tr>
          <td style="width: 12%;"><strong>Estado</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->estado }}</td>
        </tr>
        <tr>
          <td style="width: 12%;" valign="top"><strong>Responsable</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 87%;">{{ $proyecto->responsable }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Información de la transferencia:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 16%;"><strong>N° de solicitud</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 83%;">{{ $solicitud->id }}</td>
        </tr>
        <tr>
          <td style="width: 16%;" valign="top"><strong>Fecha de solicitud de transferencia</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 83%;" valign="top">{{ $solicitud->created_at }}</td>
        </tr>
        <tr>
          <td style="width: 16%;" valign="top"><strong>Justificación</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 83%;" valign="top">{{ $solicitud->justificacion }}</td>
        </tr>
        <tr>
          <td style="width: 16%;"><strong>Estado</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 83%;">{{ $solicitud->estado }}</td>
        </tr>
      </tbody>
    </table>

    <h5>III. Presupuesto:</h5>

    <table class="table">
      <thead>
        <tr>
          <th>Código</th>
          <th>Descripción</th>
          <th>Presupuesto (S/)</th>
          <th>Transferencia (S/)</th>
          <th>Nuevo presupuesto (S/)</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($partidas as $item)
          @php
            $op = '';
          @endphp
          @if ($currentTipo != $item->tipo)
            <tr>
              <td class="row-left" colspan="5">
                <strong>{{ $item->tipo }}</strong>
              </td>
            </tr>
          @endif
          @php
            if ($item->monto > $item->monto_nuevo) {
                $op = '- ' . number_format($item->monto - $item->monto_nuevo, 2);
            } elseif ($item->monto < $item->monto_nuevo) {
                $op = '+ ' . number_format($item->monto_nuevo - $item->monto, 2);
            } else {
                $op = '';
            }
          @endphp
          <tr>
            <td class="row-left">{{ $item->codigo }}</td>
            <td class="row-left">{{ $item->partida }}</td>
            <td class="row-right">{{ $item->monto }}</td>
            <td class="row-right">{{ $op }}</td>
            <td class="row-right">{{ $item->monto_nuevo }}</td>
          </tr>
          @php
            $currentTipo = $item->tipo;
          @endphp
        @endforeach
      </tbody>
    </table>

  </div>

  <script type="text/php">
    if (isset($pdf)) {
      $x = 530;
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
