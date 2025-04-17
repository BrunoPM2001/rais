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
      margin: 145px 30px 20px 30px;
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

    .tableData {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .tableData>tbody td {
      font-size: 11px;
      padding: 5px 3px 6px 3px;
    }

    .cuerpo>p {
      font-size: 11px;
    }

    .row-center {
      text-align: center;
      padding-right: 10px;
    }

    .extra-firma {
      margin-top: 50px;
      font-size: 10px;
      text-align: center;
      width: 100%;
    }

    .extra-firma2 {
      margin-top: 30px;
      font-size: 10px;
      text-align: center;
      width: 100%;
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
      evaluador
    </p>
  </div>
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <p class="titulo"><strong>Ficha de evaluación 2025 - {{ $extra->tipo_proyecto }}</strong></p>
  <div class="cuerpo">
    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 20%;" valign="top"><strong>Título</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 79%;" valign="top">{{ $extra->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 20%;" valign="top"><strong>Nombre del evaluador</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 79%;" valign="top">{{ $extra->evaluador }}</td>
        </tr>
      </tbody>
    </table>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 40%;" align="left" valign="top">Criterio de evaluación</th>
          <th style="width: 10%;" align="left" valign="top">Puntaje Máximo</th>
          <th style="width: 10%;" align="left" valign="top">Puntaje Obtenido</th>
          <th style="width: 40%;" align="left" valign="top">Comentario</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($evaluacion as $item)
          <tr>
            <td valign="top">{!! $item->opcion !!}</td>
            <td class="row-center" valign="top">{{ $item->puntaje_max }}</td>
            <td class="row-center" valign="top">{{ $item->puntaje }}</td>
            <td valign="top">{{ $item->comentario }}</td>
          </tr>
        @endforeach
        <tr>
          <td valign="top">TOTAL</td>
          <td class="row-center" valign="top">100</td>
          <td class="row-center" valign="top">{{ $total }}</td>
          <td valign="top"></td>
        </tr>
      </tbody>
    </table>

    <p><strong>Para aprobar este proyecto se necesita como mínimo 50 puntos</strong></p>

    <h6>Comentario</h5>

      <p>{{ $extra->comentario }}</p>

      <p class="extra-firma">
        <strong>Firma:</strong> ______________________
      </p>
      <p class="extra-firma2">
        <strong>Fecha:</strong> ______________________
      </p>
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
