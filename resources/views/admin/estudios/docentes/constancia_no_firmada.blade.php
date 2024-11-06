@php
  use Carbon\Carbon;

  $fecha = Carbon::now();
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Constancia</title>
  <style>
    * {
      font-family: Helvetica;
    }

    @page {
      margin: 130px 40px 20px 40px;
    }

    .head-1 {
      position: fixed;
      top: -100px;
      left: 0px;
      height: 80px;
    }

    .head-1 img {
      margin-left: 120px;
      height: 85px;
    }

    .head-2 {
      position: fixed;
      top: -100px;
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

    .titulo {
      border-top: 1px solid #000;
      border-bottom: 1px solid #000;
      padding-top: 5px;
      padding-bottom: 5px;
      font-size: 16px;
      text-align: center;
    }

    .titulo-2 {
      font-size: 14px;
      text-align: center;
    }

    .cuerpo {
      font-size: 14px;
    }

    .header-p {
      font-size: 14px;
    }

    .nombre {
      font-size: 16px;
      text-align: center;
    }

    .extra-2 {
      font-size: 13px;
      text-align: right;
      width: 100%;
    }

    .extra-firma {
      font-size: 13px;
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
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <p class="titulo"><strong>Constancia de Docente Investigador</strong></p>

  <div class="cuerpo">

    <p class="header-p">El Vicerrectorado de Investigación y Posgrado hace constar que:</p>
    <br>

    <p class="nombre">{{ $detalles->nombres }}<br>Código docente: {{ $detalles->ser_cod_ant }}</p>
    <br>

    <p class="parrafo">Cumple con los requisitos establecidos en la Directiva para Docentes Investigadores de la Universidad Nacional Mayor de San Marcos (RR N° 009077-2024-R/UNMSM y modificatoria RR N° 012521-2024-R/UNMSM) y por tanto es designado(a) como Docente Investigador.</p>

    <br>
    <p>Periodo de vigencia: {{ $detalles->fecha_constancia }} al {{ $detalles->fecha_fin }}</p>
    <p>La validez del presente documento está condicionada a la vigencia de su registro en RENACYT.</p>

    <p class="extra-2"><strong>Lima {{ $fecha->isoFormat('DD') }} de {{ ucfirst($fecha->monthName) }} de
        {{ $fecha->year }}</strong></p>

    <br>
    <br>
    <br>

    <p class="extra-firma">
      <strong>
        Dr. José Segundo Niño Montero
        <br>
        Vicerrector
      </strong>
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
