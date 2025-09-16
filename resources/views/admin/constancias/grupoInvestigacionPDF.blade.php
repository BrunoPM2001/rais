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
      margin-top: 90px;

    }

    .table-content {
      width: 100%;
      font-size: 12px;
      margin-bottom: 20px;
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
      /* Línea inferior para cada fila del cuerpo */
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

    .extra-firma {
      font-size: 14px;
    }

    .foot-1 {
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
        @if ($username)
          <span>Usuario: {{ $username }}</span>
        @endif
      </td>
    </tr>
  </table>

  <table class="cuerpo-table">
    <tr class="title">
      <td><b>Constancia de Grupos de Investigación de la<br>Universidad Nacional Mayor de San Marcos</b></td>
    </tr>
  </table>

  <table class="table-texto">
    <tr>
      <td>El Vicerrector de Investigación y Posgrado hace constar que:</td>
    </tr>
  </table>

  <table class="table-texto2">
    <tr>
      <td>Apellidos :</td>
      <td><strong>{{ $grupo[0]->apellido1 . ' ' . $grupo[0]->apellido2 }}</strong></td>
    </tr>
    <tr>
      <td>Nombres :</td>
      <td><strong>{{ $grupo[0]->nombres }}</strong></td>
    </tr>
    <tr>
      <td>Facultad :</td>
      <td><strong>{{ $grupo[0]->facultad }}</strong></td>
    </tr>
    <tr>
      <td>DNI :</td>
      <td> <strong>{{ $grupo[0]->doc_numero }}</strong></td>
    </tr>
  </table>

  <table class="table-texto3">
    <tr>
      <td>Ha registrado participación en el(los) siguiente(s) Grupo(s) de Investigación:</td>
    </tr>
  </table>

  <table class="table-content">
    <thead>
      <tr>
        <th>Nombre corto GI</th>
        <th style="text-align: center;">Nombre de Grupo</th>
        <th>Condición</th>
        <th>Resolución<br>Rectoral</th>
        <th>Fecha Creación GI</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($grupo as $item)
        <tr>
          <td>{{ $item->grupo_nombre_corto }}</td>
          <td>{{ $item->grupo_nombre }}</td>
          <td>{{ $item->condicion }}</td>
          <td>{{ $item->resolucion_rectoral }}</td>
          <td>{{ $item->resolucion_creacion_fecha }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

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
  </table>

  <div class="foot-1">
    <hr>
    <p style="padding: 0; margin:0;">Registro de Actividades de Investigación de San Marcos - © RAIS</p>
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
