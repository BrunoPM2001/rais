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
  <title>Reporte</title>

  <style>
    * {
      font-family: Arial, sans-serif;
    }

    .header-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 20px;
    }

    .header-table td {
      vertical-align: middle;
      /* Centra el contenido verticalmente */
    }

    .header-left {
      width: 14%;
      /* Espacio fijo para la izquierda */
      text-align: left;
      /* Alineación a la izquierda */
      font-size: 10px;
    }

    .header-center {
      width: 72%;
      /* Espacio amplio para la imagen */
      text-align: center;
      /* Centra el contenido */
    }

    .header-right {
      width: 14%;
      /* Espacio fijo para la derecha */
      text-align: right;
      /* Alineación a la derecha */
      font-size: 10px;
    }

    .header-center img {
      max-width: 100%;
      max-height: 100px;
      /* Controla la altura de la imagen */
      object-fit: contain;
      /* Evita la deformación */
    }

    .cuerpo-table {
      width: 100%;
      text-align: center;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 20px;
    }

    .title {
      font-size: 20px;
      text-align: center;
      margin-top: 20px;
      margin-bottom: 20px;
      color: #0a0a84;
    }

    .table-texto,
    .table-texto3 {
      width: 100%;
      font-size: 12px;
      text-align: justify;
    }

    .table-texto3 {
      margin-bottom: 20px;
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

    .table-footer {
      width: 100%;
      text-align: center;
    }

    .table-content {
      width: 100%;
      font-size: 11px;
      margin-bottom: 10px;
      border-collapse: collapse;
    }

    .table-content thead th {
      border-top: 1px solid black;
      /* Línea superior en el encabezado */
      border-bottom: 1px solid black;
      /* Línea inferior en el encabezado */
      padding: 10px;
      text-align: left;
    }

    .table-content tbody td {
      border-bottom: 1px dashed black;
      padding: 8px;

    }

    .table-content tbody tr:last-child td {
      border-bottom: 1px dashed black;
      /* Línea inferior más gruesa en la última fila */
    }

    .extra-1,
    .extra-2,
    {
    font-size: 12px;

    }

    .foot-1 {
      width: 100%;
      position: fixed;
      bottom: -20px;
      left: 0px;
      text-align: left;
      font-size: 10px;
    }

    .firma {
      width: 150px;
    }

    .sello {
      width: 100px;
    }

    .table-sello-firma {
      margin: auto;
      width: 60%;
      text-align: center;
      margin-top: 50px;
    }

    .nombre-vice {
      top: -28px;
      position: relative;
      text-align: center;
      font-size: 14px;
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
        @if ($username)
          <span>Usuario: {{ $username }}</span>
        @endif
      </td>
    </tr>
  </table>

  <table class="cuerpo-table">
    <tr class="title">
      <td><b>Constancia de registro de Libro / Capítulo de Libro</b></td>
    </tr>
  </table>
  <table class="table-texto">
    <tr>
      <td>El Vicerrector de Investigación y Posgrado hace constar que:</td>
    </tr>


    {{-- <tr>
            <td><strong>{{ $grupo[0]->nombre }}</strong>,<span> </span>{{ strtolower($grupo[0]->tipo) }} de la Facultad de
                {{ $grupo[0]->facultad }} <span> </span>ha registrado participación en el(los) siguiente(s) Grupo(s) de Investigación:</td>
        </tr> --}}

  </table>
  <table class="table-texto2">

    <tr>
      <td>Apellidos :</td>

      <td><strong>{{ $docente->apellido1 . ' ' . $docente->apellido2 }}</strong></td>
    </tr>
    <tr>
      <td>Nombres :</td>

      <td><strong>{{ $docente->nombres }}</strong></td>
    </tr>
    <tr>
      <td>Facultad :</td>
      <td><strong>{{ $docente->facultad }}</strong></td>
    </tr>
    <tr>
      <td>DNI :</td>
      <td> <strong>{{ $docente->doc_numero }}</strong></td>
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

  <table class="table-texto3">
    <tr>
      <td>Ha registrado el(los) siguiente(s) libro(s)/capítulo(s) de libro:</td>
    </tr>
  </table>

  @php $i=0; @endphp
  @foreach ($publicaciones as $libro)
    <table class="table-content">
      <thead>
        <tr>
          <th style="width: 5%;text-align: center;">N°</th>
          <th style="width: 5%;text-align: center;">Año</th>
          <th style="width: 52%;text-align: center;">Título</th>
          <th style="width: 15%;text-align: center;">Publicación</th>
          <th style="width: 15%;text-align: center;">ISSN</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="font-size: 11px; text-align: center;">{{ ++$i }}</td>
          <td style="font-size: 11px; text-align: center;">{{ $libro->periodo }}</td>
          <td style="font-size: 10px; text-align: justify;">{{ strtoupper($libro->titulo) }}</td>
          <td style="font-size: 11px; text-align: center;">{{ $libro->tipo }}</td>
          <td style="font-size: 11px; text-align: center;">{{ $libro->isbn }}</td>
        </tr>
      </tbody>
    </table>

    <p style="font-size:12px;">Resultado de proyecto de investigación financiado por:</p>

    <table class="table-content">
      <thead>
        <tr>
          <th style="width: 5%;text-align: center;">Código proyecto</th>
          <th style="width: 52%;text-align: justify;">Título del proyecto</th>
          <th style="width: 15%;text-align: center;">Entidad financiadora</th>

        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="font-size: 11px; text-align: center;">{{ $libro->codigo_proyecto }}</td>
          <td style="font-size: 10px; text-align: justify;">{{ strtoupper($libro->titulo_proyecto) }}</td>
          <td style="font-size: 11px; text-align: center;">{{ $libro->entidad_financiadora }}</td>
        </tr>
      </tbody>

    </table>
    <br>
  @endforeach

  <div style="page-break-inside: avoid;">
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

    <table class="table-sello-firma">
      <tr class="extra-firma">
        <td style="width: 100px;">
        </td>
        <td>
          <img class="firma" src="{{ public_path('firma-negra.jpg') }}" alt="Firma">
        </td>
        <td style="margin-top: 15px;">
          <img class="sello" src="{{ public_path('sello.jpg') }}" alt="Firma">
        </td>
      </tr>
    </table>

    <p class="nombre-vice">
      Dr. José Segundo Niño Montero <br><strong>Vicerrector</strong>
    </p>

  </div>

  <div class="foot-1">
    <table style="width: 80%; font-size: 10px;">
      <tr>
        <td style="width: 90%; vertical-align: top; padding-right: 10px;">
          <hr>
          Para verificar la validez de este documento puede ingresar al siguiente enlace:
          <br>
          https://rais.unmsm.edu.pe/minio/constancias/{{ $file }}
          <p style="padding-top: 5; margin: 0;">Registro de Actividades de Investigación de San Marcos - © RAIS</p>
        </td>
        <td style="width: 10%; text-align: right; vertical-align: top;">
          <img src="data:image/png;base64,{{ $qrCode }}" alt="Código QR" style="width: 50px; height: 50px;">
        </td>
      </tr>
    </table>
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

</html>
