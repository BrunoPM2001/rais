@php
  $grupoActual = '';
  $numGrupoActual = 1;
  $firstEl = 0;
  $currentTipo = '';
  $cant_miembros = 0;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Reporte</title>
  <style>
    @page {
      margin-top: 200px;
      /* espacio reservado para el encabezado */
      margin-bottom: 95px;
      /* para el pie de página */
    }

    * {
      font-family: Arial, sans-serif;
    }

    .header-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 10px;
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

    header {
      position: fixed;
      top: -170px;
      left: 0;
      right: 0;
      height: 100px;
    }

    .content {
      margin-top: 2px;
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
      margin-bottom: 5px;
      margin-top: 0px;

    }

    .title {
      font-size: 16px;
      text-align: center;
      margin-bottom: 0px;
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
      margin-top: 170px;

    }

    .table-content {
      width: 100%;
      font-size: 11px;
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
      padding: 8px;

    }

    .table-content tbody tr:last-child td {
      border-bottom: 1px solid black;
      /* Línea inferior más gruesa en la última fila */
    }

    .extra-1,
    .extra-2,
    .extra-firma {
      font-size: 12px;

    }

    .table-encabezado {
      width: 100%;
      border-collapse: collapse;
      /* Elimina el espacio extra entre celdas */
    }

    /* Aplica una línea arriba y otra abajo al thead */
    .table-encabezado thead tr {
      border-top: 1px solid #000;
      border-bottom: 1px solid #000;
    }

    .table-encabezado thead th {
      text-align: left;
      padding: 5px;
    }

    .table-encabezado tbody td {
      padding: 2px 4px;
      /* Reduce el espacio interno */
      line-height: 1.2;
      /* Reduce la altura de línea */
    }
  </style>
</head>


<body>
  <header>
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
          <span>Usuario: {{ $admin->nombres }}</span>
        </td>
      </tr>
    </table>
    <div style="border-top: 2px solid black; margin: 5px 0;"></div>
    <table class="cuerpo-table">
      <tr class="title">
        <td><b>GRUPO DE INVESTIGACIÓN <br>Año - {{ date('Y') }}</b></td>
      </tr>
    </table>
    <table class="table-texto" style="width: 100%; font-size:12px; margin-bottom: 0px;">
      <tr>
        <td style="text-align: left; width: 45%;">
          Área: {{ $area->sigla . ' ' . $area->nombre }}
        </td>
        <td style="text-align: right; width: 55%;">
          Facultad: {{ $area->facultad }}
        </td>
      </tr>
    </table>
    <div style="border-top: 1px solid black; margin: 0px 0;"></div>
  </header>

  <div class="div"></div>

  <div class="foot-1" style="position: fixed; bottom: -60px; width: 100%;">
    <div style="border-top: 2px solid black; margin: 1px 0;width:40%;"></div>
    <table style="width: 100%; font-size: 9px; font-style: italic; table-layout: fixed;">
      <tr>
        <td style="width: 90%; text-align: justify; line-height: 1.3;">
          Esta es una copia auténtica e imprimible de un documento electrónico resguardado por la Universidad
          Nacional Mayor de San Marcos, a través del Vicerrectorado de Investigación y Posgrado (VRIP),
          correspondiente a Grupos de investigación. Su autenticidad e integridad pueden ser verificadas a
          través del siguiente enlace web:
          <span style="font-size: 8px;">https://rais.vrip.unmsm.edu.pe</span>
        </td>
        <td style="width: 10%; text-align: center;">
          <img src="data:image/png;base64,{{ $qr }}" alt="Código QR" style="width: 30px; height: 30px;">
        </td>
      </tr>
    </table>
  </div>

  <div class="content">
    @if (!empty($detalle))
      <div
        style="
      max-width: 100%;
      margin: 8px 0;
      background-color: #f9f9f9;
      border: 1px solid #ccc;
      padding: 5px;
      font-size: 12px;
      border-radius: 6px;
      white-space: pre-wrap; /* 👈 Forzar salto en palabras largas */
      word-break: break-all;  /* 👈 Funciona en dompdf */
    ">
        {{ $detalle }}
      </div>
    @endif


    @foreach ($lista as $item)
      @if ($grupoActual != $item->grupo_nombre)
        @if ($firstEl == 1)
          </tbody>
          </table>
          <div style="border-top: 1px solid black; margin: 1px 0;width:100%;"></div>
          <p style="padding-top:1px;margin-top:1px;"><strong style="font-size: 12px;">Total de integrantes:
              {{ $cant_miembros }}</strong></p>
        @endif
        @php
          $cant_miembros = 0;
          $firstEl = 1;
        @endphp
        <table
          style="
                width: 100%;
                margin:0 5px 0 0;
                background-color: #e0e0e0;
                border: 1px solid #ccc;
                padding: 0px;
                font-size:10px;
                border-collapse: collapse;">
          <thead>
            <tr>
              <th style="width: 5%;">Nro.</th>
              <th style="width: 15%;">Nombre corto GI</th>
              <th style="width: 60%;">Nombre de Grupo</th>
              <th style="width: 10%;">Estado</th>
              <th style="width: 20%;">Categoría</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="text-align: center;">{{ $numGrupoActual }}</td>
              <td style="text-align: center;">{{ mb_strtoupper($item->grupo_nombre_corto, 'UTF-8') }}
              </td>
              <td style="text-align: center;">{{ mb_strtoupper($item->grupo_nombre, 'UTF-8') }}</td>
              <td style="text-align: center;">
                @switch($item->estado)
                  @case(1)
                    Reconocido
                  @break

                  @case(2)
                    Observado
                  @break

                  @case(4)
                    Registrado
                  @break

                  @case(5)
                    Enviado
                  @break

                  @case(6)
                    En proceso
                  @break
                @endswitch
              </td>
              <td style="text-align: center;">{{ $item->grupo_categoria }}</td>
            </tr>
          </tbody>
        </table>
        <table class="table-encabezado" style="font-size:9px; padding-left:45px;">
          <thead>
            <tr>
              <th style="width: 15%;">Condición</th>
              <th style="width: 10%;">Código</th>
              <th style="width: 40%;">Nombre</th>
              <th style="width: 20%;">Tipo</th>
              <th style="width: 30%;">Facultad</th>
            </tr>
          </thead>
          <tbody>
            @php
              $numGrupoActual++;
            @endphp
      @endif
      @if ($currentTipo != $item->condicion)
        <tr>
          <td style="font-style: italic;"><b>{{ $item->condicion }}</b></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      @endif
      <tr>
        <td></td>
        <td>{{ $item->codigo }}</td>
        <td style="text-transform: uppercase">{{ mb_strtoupper($item->nombre, 'UTF-8') }}</td>
        <td>{{ $item->tipo }}</td>
        <td>{{ $item->facultad_miembro }}</td>
      </tr>
      @php
        $cant_miembros++;
        $currentTipo = $item->condicion;
        $grupoActual = $item->grupo_nombre;
      @endphp

      @if ($loop->last)
        </tbody>
        </table>
        <div style="border-top: 1px solid black; margin: 1px 0;width:100%;"></div>
        <p style="padding-top:1px;margin-top:1px;"><strong style="font-size: 12px;">Total de integrantes:
            {{ $cant_miembros }}</strong></p>
      @endif
    @endforeach
  </div>

  <script type="text/php">
    if (isset($pdf)) {
      $x = 270;
      $y = 814;
      $text = "{PAGE_NUM} de {PAGE_COUNT}";
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
