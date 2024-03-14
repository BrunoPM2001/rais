@php
  use Carbon\Carbon;

  $fecha = Carbon::now();
  $currentTipo = '';
  $puntajetotal = 0.0;
  $titulo = '';
  $subtitulo = '';
  $firstEl = 0;

  //  Calcular el puntaje total
  foreach ($publicaciones as $item) {
      $puntajetotal += $item->puntaje;
  }
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
      background: red;
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

    .texto-1 {
      font-size: 13px;
      margin: 20px 0 0 0;
    }

    .texto-2 {
      display: inline-block;
      font-size: 13px;
      margin: 0 0 20px 0;
    }

    .subhead {
      width: 100%;
      margin-top: 0;
      margin-bottom: 0;
      font-size: 14px;
      text-align: center;
    }

    .table {
      width: 100%;
      border-collapse: separate;
      margin-bottom: 30px;
    }

    .table>thead {
      font-size: 12px;
      font-weight: bold;
      border-top: 1.5px solid #000;
      border-bottom: 1.5px solid #000;
    }

    .table>tbody td {
      font-size: 12px;
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

    .cuerpo {
      border-bottom: 2px solid black;
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

  <div class="cuerpo">
    <p class="titulo"><strong>Constancia de Registro de Publicaciones Científicas</strong></p>
    <p class="texto-1">
      El Vicerrector de Investigación y Posgrado de la Universidad Nacional Mayor de
      San Marcos hace constar que:
    </p>
    <p class="texto-2">
      El Profesor(a): <strong>{{ $docente->nombre }}</strong>
      <br>
      de la facultad de: <strong>{{ $docente->facultad }}</strong>
      <br>
      ha registrado las siguiente publicaciones:
    </p>
    <div class="caja-resumen">
      <p class="resumen-1">
        N° de publicaciones:
        <br>
        Puntaje total:
      </p>
      <p class="resumen-2">
        <strong>
          {{ sizeof($publicaciones) }}
          <br>
          {{ $puntajetotal }}
        </strong>
      </p>
    </div>
  </div>
  <div>
    @foreach ($publicaciones as $publicacion)
      @if ($publicacion->categoria != $subtitulo)
        @if ($firstEl == 1)
          </tbody>
          </table>
        @endif
        @php
          $firstEl = 1;
        @endphp
      @endif

      @if ($publicacion->tipo != $titulo)
        <p class="tab-titulo">{{ $publicacion->tipo }}</p>
      @endif

      @if ($publicacion->categoria != $subtitulo)
        <p class="tab-subtitulo">{{ $publicacion->categoria }}</p>
        <table class="table">
          <thead>
            <tr>
              @switch($publicacion->tipo)
                @case('Artículo en Revista')
                  <th style="width: 5%;">Año</th>
                  <th style="width: 45%;">Título</th>
                  <th style="width: 25%;">Publicación</th>
                  <th style="width: 10%;">ISSN</th>
                  <th style="width: 15%;">Obs</th>
                @break

                @case('Libro')
                  <th style="width: 5%;">Año</th>
                  <th style="width: 55%;">Título</th>
                  <th style="width: 20%;">ISBN</th>
                  <th style="width: 20%;">Obs</th>
                @break

                @case('Tesis')
                  <th style="width: 5%;">Año</th>
                  <th style="width: 45%;">Título</th>
                  <th style="width: 25%;">Universidad</th>
                  <th style="width: 15%; text-align: left;">País</th>
                  <th style="width: 10%;">Obs</th>
                @break

                @case('Tesis asesoria')
                  <th style="width: 5%;">Año</th>
                  <th style="width: 45%;">Título</th>
                  <th style="width: 25%;">Universidad</th>
                  <th style="width: 15%; text-align: left;">País</th>
                  <th style="width: 10%;">Obs</th>
                @break
              @endswitch
            </tr>
          </thead>
          <tbody>
      @endif

      <tr>
        @switch($publicacion->tipo)
          @case('Artículo en Revista')
            <td style="text-align: center;">{{ $publicacion->año }}</td>
            <td>{{ $publicacion->titulo }}</td>
            <td>{{ $publicacion->publicacion_nombre }}</td>
            <td style="text-align: center;">{{ $publicacion->issn }}</td>
            <td>{{ $publicacion->observaciones_usuario }}</td>
          @break

          @case('Libro')
            <td style="text-align: center;">{{ $publicacion->año }}</td>
            <td>{{ $publicacion->titulo }}</td>
            <td style="text-align: center;">{{ $publicacion->isbn }}</td>
            <td>{{ $publicacion->observaciones_usuario }}</td>
          @break

          @case('Tesis')
            <td style="text-align: center;">{{ $publicacion->año }}</td>
            <td>{{ $publicacion->titulo }}</td>
            <td>{{ $publicacion->universidad }}</td>
            <td>{{ $publicacion->pais }}</td>
            <td>{{ $publicacion->observaciones_usuario }}</td>
          @break

          @case('Tesis asesoria')
            <td style="text-align: center;">{{ $publicacion->año }}</td>
            <td>{{ $publicacion->titulo }}</td>
            <td>{{ $publicacion->universidad }}</td>
            <td>{{ $publicacion->pais }}</td>
            <td>{{ $publicacion->observaciones_usuario }}</td>
          @break
        @endswitch
      </tr>

      @php
        $titulo = $publicacion->tipo;
        $subtitulo = $publicacion->categoria;
      @endphp
    @endforeach
  </div>

  <p class="extra-1"><strong>Se expide la presente constancia a solicitud de interesado(a).</strong></p>
  <br>
  <p class="extra-2"><strong>Lima {{ $fecha->isoFormat('DD') }} de {{ ucfirst($fecha->monthName) }} de
      {{ $fecha->year }}</strong></p>
  <br>
  <p class="extra-firma">
    <strong>
      Dr. José Segundo Niño Montero
      <br>
      Vicerrector
    </strong>
  </p>

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
