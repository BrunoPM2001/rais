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

  foreach ($patentes as $patente) {
      $puntajetotal += $patente->puntaje;
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
      font-family: Arial, sans-serif;
    }

    /* @page {
         margin: 145px 20px 20px 20px;
            } */

    .header-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 20px;
    }

    .header-table td {
      vertical-align: middle;
    }

    .header-left {
      width: 14%;
      text-align: left;
      font-size: 10px;
    }

    .header-center {
      width: 72%;
      text-align: center;
    }

    .header-right {
      width: 14%;
      text-align: right;
      font-size: 10px;
    }

    .header-center img {
      max-width: 100%;
      max-height: 100px;
      object-fit: contain;

    }

    .cuerpo-table {
      width: 100%;
      font-size: 18px;
      text-align: center;
      margin-top: 15px;
      color: #0a0a84;
    }

    .table-texto {
      width: 100%;
      margin-top: 30px;
      margin-bottom: 30px;
      font-size: 13px;
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
      bottom: -20px;
      left: 0px;
      text-align: left;
      font-size: 10px;
    }

    .texto-1 {
      font-size: 12px;
      margin: 15px 0 0 0;
    }


    .texto-2 {
      display: inline-block;
      font-size: 12px;
      margin: 0 0 20px 0;
    }

    .table-texto2 {
      width: 100%;
      table-layout: fixed;

    }

    .table-texto2 td {
      text-align: left;
      font-size: 12px;

    }

    .table-texto2 td:first-child {
      text-align: right;
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
      margin-bottom: 10px;
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

    /* .extra-2 {
            font-size: 11px;
            text-align: right;
            width: 100%;
        } */

    .extra-firma {
      font-size: 11px;
      text-align: center;
      width: 100%;
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
      padding-top: 5px;
      margin-top: 5px;

    }

    .table-footer {
      width: 100%;
      text-align: center;
      margin-top: 90px;

    }

    .extra-1,
    .extra-2,
    {
    font-size: 12px;

    }

    .extra-firma {
      font-size: 14px;
    }

    .foot-1 {
      width: 100%;
      position: fixed;
      bottom: -20px;
      left: 0px;
      text-align: left;
      font-size: 10px;
    }
  </style>
</head>

<body>
  <table class="header-table">
    <tr>
      <td class="header-left">
        <span>Fecha: {{ date('d/m/Y') }}</span><br>
        <span>Hora: {{ date('H:i:s') }}</span>
      </td>
      <td class="header-center">
        <img src="{{ public_path('head-pdf.jpg') }}" alt="Header">
      </td>
      <td class="header-right">
        <span>© RAIS</span><br>
        <span>Usuario: OMontes</span>
      </td>
    </tr>
  </table>

  <table class="cuerpo-table">
    <tr class="title">
      <td><b>Constancia de Registro de Publicaciones Científicas</b></td>
    </tr>
  </table>


  <table class="texto-1">
    <tr>
      <td> El Vicerrector de Investigación y Posgrado de la Universidad Nacional Mayor de
        San Marcos hace constar que:</td>
    </tr>
  </table>

  <table style="width: 100%;  font-size: 12px; margin-top: 15px;">
    <tr>
      <!-- Primera columna (50%) -->
      <td style="width: 65%; vertical-align: top;border-collapse:collapse; padding:10px;border: 1px solid #111111;">
        <table style="width: 100%; height: 70px; height: 70px;">
          <tr>
            <td>Apellidos:</td>
            <td><strong>{{ $docente->apellido1 . ' ' . $docente->apellido2 }}</strong></td>
          </tr>
          <tr>
            <td>Nombres:</td>
            <td><strong>{{ $docente->nombres }}</strong></td>
          </tr>
          <tr>
            <td>Facultad:</td>
            <td><strong>{{ $docente->facultad }}</strong></td>
          </tr>
          <tr>
            <td>DNI:</td>
            <td><strong>{{ $docente->doc_numero }}</strong></td>
          </tr>
          <tr>
            <td>Categoria :</td>
            <td> <strong>{{ $docente->categoria }}</strong></td>
          </tr>
          <tr>
            <td>Clase :</td>
            <td> <strong>{{ $docente->clase }}</strong></td>
          </tr>
        </table>
      </td>

      <!-- Segunda columna (50%) -->
      <td style="width: 35%; vertical-align: middle; border-collapse: collapse; border: 1px solid #111111">
        <table style="width: 100%; padding: 10px; height: 70px; border-collapse: collapse;">
          <tr>
            <td>Numero de publicaciones:</td>
            <td><strong>{{ sizeof($publicaciones) + sizeof($patentes) }}</strong></td>
          </tr>
          <tr>
            <td>Puntaje total:</td>
            <td><strong>{{ number_format($puntajetotal, 2) }}</strong></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>


  <table class="texto-1">
    <tr>
      <td>Ha registrado las siguiente publicaciones:</td>
    </tr>
  </table>

  <div>
    @php
      $titulo = null;
      $subtitulo = null;
      $firstEl = 0;
    @endphp

    @foreach ($publicaciones as $publicacion)
      {{-- Validar tipo y categoría antes de mostrar encabezados --}}
      @if (!empty($publicacion->categoria) && $publicacion->categoria != $subtitulo)
        @if ($firstEl == 1)
          </tbody>
          </table>
        @endif
        @php
          $firstEl = 1;
        @endphp
      @endif

      @if (!empty($publicacion->tipo) && $publicacion->tipo != $titulo)
        @if (!empty($publicacion->tipo))
          <table style="width: 100%; margin: 0; padding: 0;">
            <tr>
              <td colspan="100%" style="margin: 0; padding: 0;">
                <h4
                  style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px;margin-bottom: 3px;">
                  {{ $publicacion->tipo }}
                </h4>
              </td>
            </tr>
          </table>
        @endif
      @endif

      @if (!empty($publicacion->categoria) && $publicacion->categoria != $subtitulo)
        @if (!empty($publicacion->categoria))
          <p class="tab-subtitulo" style="margin-top:0;padding-top=0;">{{ $publicacion->categoria }}</p>

          <table class="table">
            <thead>
              <tr>
                @switch($publicacion->tipo)
                  @case('Artículo en Revista')
                    <th style="width: 5%;">Año</th>
                    <th style="width: 52%;">Título</th>
                    <th style="width: 18%;">Publicación</th>
                    <th style="width: 10%;">ISSN</th>
                  @break

                  @case('Libro')
                    <th style="width: 5%;">Año</th>
                    <th style="width: 55%;">Título</th>
                    <th style="width: 30%;text-align: center;">Publicación</th>
                    <th style="width: 20%;">ISBN</th>
                  @break

                  @case('Libro de Resúmenes')
                    <th style="width: 5%;">Año</th>
                    <th style="width: 55%;">Título</th>
                    <th style="width: 30%;">Libro</th>
                    <th style="width: 10%;">Ciudad</th>
                  @break

                  @case('Capítulo en Libro')
                    <th style="width: 5%;">Año</th>
                    <th style="width: 55%;">Título</th>
                    <th style="width: 30%;">Capítulo</th>
                  @break

                  @case('Tesis')
                    <th style="width: 5%;">Año</th>
                    <th style="width: 45%;">Título</th>
                    <th style="width: 25%;">Universidad</th>
                    <th style="width: 15%; text-align: center;">País</th>
                  @break

                  @case('Tesis asesoria')
                    <th style="width: 5%;">Año</th>
                    <th style="width: 45%;">Título</th>
                    <th style="width: 25%;">Universidad</th>
                    <th style="width: 15%; text-align: center;">País</th>
                  @break
                @endswitch
              </tr>
            </thead>
            <tbody>
        @endif
      @endif

      {{-- Mostrar fila si la publicación tiene datos válidos --}}
      @if (!empty($publicacion->tipo) && !empty($publicacion->titulo))
        <tr>
          @switch($publicacion->tipo)
            @case('Artículo en Revista')
              <td style="text-align: center;">{{ $publicacion->año }}</td>
              <td style="font-size: 11px; text-align: justify;">{{ $publicacion->titulo }}</td>
              <td style="font-size: 11px; text-align: left;">{{ $publicacion->publicacion_nombre }}</td>
              <td style="text-align: center;">{{ $publicacion->issn }}</td>
            @break

            @case('Libro')
              <td style="text-align: center;">{{ $publicacion->año }}</td>
              <td style="font-size: 11px; text-align: justify;">{{ $publicacion->titulo }}</td>
              <td style="font-size: 11px; text-align: center;">{{ $publicacion->publicacion_nombre }}</td>
              <td style="text-align: center;">{{ $publicacion->isbn }}</td>
            @break

            @case('Libro de Resúmenes')
              <td style="text-align: center;">{{ $publicacion->año }}</td>
              <td style="font-size: 11px; text-align: justify;">{{ $publicacion->titulo }}</td>
              <td style="font-size: 11px; text-align: left;">{{ $publicacion->publicacion_nombre }}</td>
              <td style="font-size: 11px; text-align: center;">{{ $publicacion->lugar_publicacion }}</td>
            @break

            @case('Capítulo en Libro')
              <td style="text-align: center;">{{ $publicacion->año }}</td>
              <td style="font-size: 11px; text-align: justify;">{{ $publicacion->titulo }}</td>
              <td style="font-size: 11px; text-align: left;">{{ $publicacion->publicacion_nombre }}</td>
            @break

            @case('Tesis')
              <td style="text-align: center;">{{ $publicacion->año }}</td>
              <td style="font-size: 11px; text-align: justify;">{{ $publicacion->titulo }}</td>
              <td style="font-size: 11px;text-align: left;">{{ $publicacion->universidad }}</td>
              <td style="font-size: 11px;text-align: center;">{{ $publicacion->pais }}</td>
            @break

            @case('Tesis asesoria')
              <td style="text-align: center;">{{ $publicacion->año }}</td>
              <td style="font-size: 11px; text-align: justify;">{{ $publicacion->titulo }}</td>
              <td style="font-size: 11px;text-align: left;">{{ $publicacion->universidad }}</td>
              <td style="font-size: 11px;text-align: center;">{{ $publicacion->pais }}</td>
            @break
          @endswitch
        </tr>
      @endif

      {{-- Actualizar títulos y subtítulos --}}
      @php
        $titulo = $publicacion->tipo ?? null;
        $subtitulo = $publicacion->categoria ?? null;
      @endphp
    @endforeach



  </div>

  {{-- @if (!empty($patentes->tipo)) --}}
  <table style="width: 100%; margin: 0; padding: 0;">
    <tr>
      <td colspan="100%" style="margin: 0; padding: 0;">
        <h4
          style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px;margin-bottom: 3px;">
          Patente
        </h4>
      </td>
    </tr>
  </table>
  <table class="table">
    <thead>
      <tr>
        <th style="font-size: 11px; text-align: center;">Año</th>
        <th style="font-size: 11px; text-align: center;">Título</th>
        <th style="font-size: 11px; text-align: center;">Lugar registro</th>
        <th style="font-size: 11px; text-align: center;">Titular</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($patentes as $patente)
        <tr>
          <td style="font-size: 11px;">{{ $patente->año }}</td>
          <td style="font-size: 11px; text-align: justify;">{{ $patente->titulo }}</td>
          <td style="font-size: 11px;">{{ $patente->oficina_presentacion }}</td>
          <td style="font-size: 11px;">{{ $patente->titular }}</td>
      @endforeach

    </tbody>
  </table>
  {{-- @endif --}}

  <table>
    <tr>
      <td class="extra-1">Se expide la presente constancia a solicitud del(de la) interesado(a) para los fines que
        considere conveniente.</td>
    </tr><br>
    <tr>
      <td class="extra-2">Lima, {{ $fecha->isoFormat('DD') }} de {{ ucfirst($fecha->monthName) }}
        de {{ $fecha->year }}</td>
    </tr>
  </table>
  <table class="table-footer">
    <tr class="extra-firma">
      <td>Dr. José Segundo Niño Montero <br><strong>Vicerrector</strong></td>
    </tr>
    <div class="foot-1">
      <table style="width: 80%; font-size: 10px;">
        <tr>
          <td style="width: 90%; vertical-align: top; padding-right: 10px;">
            <hr>
            Para verificar la validez de este documento puede ingresar al siguiente enlace:
            <br>
            http://localhost:9000/repo/{{ $file }}
            <p style="padding-top: 5; margin: 0;">Registro de Actividades de Investigación de San Marcos - © RAIS</p>
          </td>
          <td style="width: 10%; text-align: right; vertical-align: top;">
            <img src="data:image/png;base64,{{ $qrCode }}" alt="Código QR" style="width: 50px; height: 50px;">
          </td>
        </tr>
      </table>
    </div>
  </table>
  <div class="foot-1">
    <hr>
    <p style="padding: 0; margin:0;">Registro de Actividades de Investigación de San Marcos - ©RAIS</p>
  </div>

  <script type="text/php">
        if (isset($pdf)) {
          $x = 525;
          $y = 810;
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
