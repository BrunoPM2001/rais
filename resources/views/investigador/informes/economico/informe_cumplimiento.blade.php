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

    .row-center {
      text-align: center;
    }

    .row-right {
      text-align: right;
      padding-right: 10px;
    }

    .cuerpo>p {
      font-size: 11px;
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
      INFORME DE CUMPLIMIENTO DE LOS DOCENTES INVESTIGADORES DEL PROYECTO CON FINANCIAMIENTO PARA GI
    </strong>
  </p>
  <div class="cuerpo">


    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 15%;"><strong>Fecha de envío</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 84%;">{{ $datos->fecha }}</td>
        </tr>
      </tbody>
    </table>

    <p style="text-align: justify">
      Yo <strong>{{ $responsable->nombres }}</strong>, responsable del proyecto <strong>{{ $datos->titulo }}</strong>
      , informo sobre las actividades de los docentes investigadores de mi proyecto:
    </p>

    <br>
    <br>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 80%;">Docente Investigador participante</th>
          <th style="width: 20%;">Cumplimiento</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($listado as $item)
          <tr>
            <td class="row-left">{{ $item->nombres }}</td>
            <td class="row-center">{{ $item->cumplimiento }}</td>
          </tr>
          <tr>
            <td class="row-left" colspan="2"><strong>{{ $item->actividad }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>


    <br>
    <br>
    <br>
    <br>
    <br>

    <p class="extra-firma">
      <strong>
        Responsable de Proyecto de Investigación
        <br>
        con Financiamiento monetario
        <br>
        (Firma)
      </strong>
    </p>

    <br>
    <br>


    <p style="text-align: justify">
      <strong>(1): Procedimiento para acceder al programa de proyectos de Investigación para Grupos de Investigación</strong> 
      - Artículo 11° Asignación del fondo; c. El incentivo será pagado previo informe del responsable sobre el cumplimiento de 
      cada investigador del proyecto, según la disponibilidad presupuestal y lo establecido por el VRIP
      <br>
      <br>
      <strong>(2):</strong> Directiva para la rendición económica de los fondos otorgados por la UNMSM a las actividades de investigación
      <br>
      <br>
      <strong>NOTA:</strong> Sírvase adjuntar este informe al informe económico al 100 %.
    </p>

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
