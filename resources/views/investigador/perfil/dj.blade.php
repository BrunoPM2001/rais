<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Declaración jurada</title>
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
      font-size: 20px;
      text-align: center;
      color: #3b5ab1
    }

    .cuerpo>p {
      padding: 0px 30px 0px 30px;
      font-size: 14px;
      text-align: justify;
    }

    .center-bold {
      text-align: center;
      margin: auto;
      font-size: 14px;
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
      investigador
    </p>
  </div>
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <p class="titulo"><strong>DECLARACIÓN JURADA</strong></p>
  <div class="cuerpo">
    <p>
      Quien suscribe, identificado/a con DNI N° {{ $data->ser_cod }}, docente ordinario, {{ $data->categoria }} y a
      {{ $data->clase }}, de la Facultad de {{ $data->facultad }}, Departamento Académico
      {{ $data->des_dep_cesantes }}, declaro no haber incurrido en algún tipo de infracción o tener alguna sanción
      vigente, conforme lo establecido en el Código de Integridad Científica del Consejo Nacional de Ciencia, Tecnología
      e Innovación Tecnológica (CONCYTEC). En caso contrario, me hago responsable de las sanciones establecidas en el
      referido documento, en fe de lo cual suscribo la presente.
    </p>

    <p>
      Por tanto, declaro haber leído y cumplir con las condiciones establecidas en esta Declaración Jurada.
    </p>

    <p>
      Lima, {{ $fecha }}
    </p>
  </div>
  <br />
  <p class="center-bold"><strong>{{ $data->ser_ape_pat }} {{ $data->ser_ape_mat }} {{ $data->ser_nom }}</strong></p>
  <p class="center-bold">DNI: <strong>{{ $data->ser_cod }}</strong></p>

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
