@php
  $currentTipo = '';
  $puntajetotal = 0.0;
  $titulo = '';
  $subtitulo = '';
  $firstEl = 0;

  //  Calcular el puntaje total
  // foreach ($publicaciones as $item) {
  //     $puntajetotal += $item->puntaje;
  // }

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
      margin-bottom: 30px;
    }

    .table>thead th {
      font-size: 10px;
      font-weight: bold;
      border-top: 1.5px solid #000;
      border-bottom: 1.5px solid #000;
    }

    .table>tbody td {
      font-size: 10px;
      padding: 5px 3px 6px 3px;
      border-bottom: 1px dashed #000;
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

    .caja-resumen {
      float: right;
      border: 1px solid black;
      margin: 5px 0 5px 0;
      padding: 10px 10px 0 10px;
    }

    .resumen-1 {
      font-size: 13px;
      display: inline-block;
      text-align: right;
      margin: 0;
      padding: 0;
    }

    .resumen-2 {
      font-size: 13px;
      display: inline-block;
      text-align: left;
      margin: 0;
      padding: 0;
    }

    .cuerpo>p {
      font-size: 11px;
    }

    .tab-titulo {
      font-size: 13px;
      font-weight: bold;
      text-align: left;
    }

    .tab-subtitulo {
      font-size: 13px;
      font-weight: bold;
      text-align: right;
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
      ichajaya
    </p>
  </div>
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <p class="titulo"><strong>Reporte de publicación</strong></p>
  <div class="cuerpo">

    <h5>I. Información general:</h5>

    <p><b>Código de publicación: </b> $proyecto->grupo_nombre</p>
    <p><b>Título: </b>proyecto->facultad_nombre</p>
    <p><b>Repositorio de la tesis(Link): </b>$proyecto->area_nombre</p>
    <p><b>Tipo de tesis: </b>$proyecto->codigo_proyect</p>
    <p><b>Año de publicación: </b>$proyecto->linea_nombre</p>
    <p><b>Total de páginas: </b>$proyecto->objetivo</p>
    <p><b>Universidad: </b>$proyecto->linea </p>
    <p><b>Ciudad: </b>$proyecto->localizacion </p>
    <p><b>País: </b>$proyecto->localizacion </p>
    <p><b>Palabras clave: </b>$proyecto->localizacion </p>
    <p><b>Estado: </b>$proyecto->localizacion </p>

    <h5>II. Resultado de proyectos de investigación financiados por:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 15%;" align="left">Código de proyecto</th>
          <th style="width: 70%;" align="left">Título</th>
          <th style="width: 15%;" align="left">Entidad financiadora</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>C234234ASD</td>
          <td>El proyecto más grande gaa</td>
          <td>UNMSM</td>
        </tr>
      </tbody>
    </table>

    <h5>III. Autores:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 20%;" align="left">Tipo de integrante</th>
          <th style="width: 30%;" align="left">Nombre en la publicación</th>
          <th style="width: 30%;" align="left">Nombre del autor</th>
          <th style="width: 20%;" align="left">Relación UNMSM</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Asesor</td>
          <td>Jgamboa</td>
          <td>GUZMAN DUXTAN ALDO JAVIER </td>
          <td>DOCENTE PERMANENTE</td>
        </tr>
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
