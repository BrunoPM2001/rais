@php
  $tituloActual = '';
  $numTituloActual = 1;
  $firstEl = 0;
  $currentTipo = '';
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
      margin-bottom: 80px;
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

    /* .foot-1 {
            position: fixed;
            bottom: 60px;
            left: 0px;
            text-align: left;
            font-size: 10px;
        } */

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
  @php
    // Variables de control para evitar repeticiones
    $currentGroup = null; // Para controlar el grupo
    $currentProject = null; // Para controlar el proyecto
    $currentTipo = null; // Para controlar el "Condición" del proyecto
    $numProyecto = 1; // Contador de proyectos
    $firstEl = false; // Para saber si ya hemos impreso al menos un proyecto
    $numProyecto = 1;
  @endphp
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
        <td><b>{{ $tipo }} <br>Año {{ $periodo }}</b></td>
      </tr>
    </table>
    <table class="table-texto" style="width: 100%; font-size:12px; margin-bottom: 0px;">
      <tr>
        <td style="text-align: left; width: 70%;">
          Área: {{ $area->sigla . ' ' . $area->nombre }}
        </td>
        <td style="text-align: right; width: 30%;">
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
          correspondiente a proyectos ganadores aprobados mediante Resolución Rectoral. Su autenticidad e
          integridad
          pueden ser verificadas a través del siguiente enlace web:
          <span style="font-size: 8px;">https://rais.vrip.unmsm.edu.pe</span>
        </td>
        <td style="width: 10%; text-align: center;">
          <img src="data:image/png;base64,{{ $qr }}" alt="Código QR" style="width: 30px; height: 30px;">
        </td>
      </tr>
    </table>
  </div>


  <div class="content">
    @foreach ($lista as $item)
      {{-- Verificamos si cambió de grupo (g.id). Si cambió, imprimimos el bloque de "Área / Facultad / Grupo" --}}
      @if ($currentGroup != $item->grupo_id)
        {{-- Cerramos tablas anteriores si no es la primera vez que imprimimos --}}
        @if ($firstEl)
          </tbody>
          </table>
        @endif
        @if ($currentGroup != null)
          <div style="border-top: 1px solid black; margin: 0px 0;"></div>
        @endif

        {{-- Nombre del grupo (solo se imprime cuando cambia de grupo) --}}
        <table
          style="
                        width: 100%;
                        margin:0 5px 0 0;
                        background-color: #e0e0e0;
                        border: 1px solid #ccc;
                        padding: 0px;
                        font-size:10px;
                        border-collapse: collapse;">
          <tr>
            <td style="padding: 5px;">
              Nombre del grupo:
              <strong>{{ $item->grupo_nombre . ' (' . mb_strtoupper($item->grupo_nombre_corto, 'UTF-8') . ')' }}</strong>
            </td>
          </tr>
        </table>

        @php
          $currentGroup = $item->grupo_id; // Actualizamos el grupo actual
          // Reseteamos variables para que se imprima el siguiente proyecto desde cero
          $currentProject = null;
        @endphp
      @endif

      {{-- Verificamos si cambió de proyecto (p.titulo). Si cambió, imprimimos el bloque de "Proyecto" --}}
      @if ($currentProject != $item->titulo)
        {{-- Cerramos la tabla de proyecto anterior, si no es la primera vez --}}
        @if ($firstEl)
          </tbody>
          </table>
        @endif

        {{-- Encabezado del nuevo proyecto --}}
        <table class="table-encabezado" style="font-size:9px;">
          <thead>
            <tr>
              <th style="width: 1%;">N°</th>
              <th style="width: 4%;">Código</th>
              <th style="width: 70%; text-align: center;">Título del proyecto</th>
              <th style="width: 10%;">Presupuesto</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ $numProyecto }}</td>
              <td>{{ $item->codigo_proyecto }}</td>
              <td style="font-style: italic;"><b>{{ mb_strtoupper($item->titulo, 'UTF-8') }}</b></td>
              <td><b>S/ {{ number_format($item->presupuesto, 2, '.', ',') }}</b></td>
            </tr>
          </tbody>
        </table>
        @php
          $numProyecto++;
        @endphp

        {{-- Tabla para la lista de integrantes / condiciones --}}
        <table class="table-encabezado" style="font-size: 9px; padding-left:75px;">
          <thead>
            <tr>
              <th style="width: 22%;">Condición</th>
              <th style="width: 45%;">Apellidos y nombres</th>
              <th style="width: 24%;">Tipo</th>
              <th style="width: 30%;">Facultad</th>
              <th style="width: 18%;">Condición en GI</th>
            </tr>
          </thead>
          <tbody>
            {{-- Continúa la lista de integrantes dentro de esta tbody --}}
      @endif

      {{-- Verificamos si cambió la "Condición" (p.ej. Titular, Miembro, etc.) para mostrarla como título de bloque --}}
      @if ($currentTipo != $item->condicion)
        <tr>
          <td style="font-style: italic;"><b>{{ $item->condicion }}</b></td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
        @php
          $currentTipo = $item->condicion;
        @endphp
      @endif

      {{-- Mostramos los datos del integrante (filas repetidas) --}}
      <tr>
        <td style="padding-left:35px;">{{ $item->codigo }}</td>
        <td style="font-size:8px;">{{ mb_strtoupper($item->nombres, 'UTF-8') }}</td>
        <td style="font-size:8px;">{{ $item->tipo_investigador }}</td>
        <td>{{ $item->facultad_miembro }}</td>
        <td>{{ $item->condicion_gi }}</td>
      </tr>

      {{-- Actualizamos variables para siguiente iteración --}}
      @php
        $currentProject = $item->titulo;
        $firstEl = true; // Ya hemos impreso al menos un proyecto
      @endphp

      {{-- 
                Si el siguiente registro sigue siendo el mismo proyecto, se mantendrá en la misma tabla. 
                Si cambia de proyecto, en la próxima iteración se cerrará y abrirá otra. 
                Si cambia de grupo, también se mostrará Área/Facultad/Grupo de nuevo.
            --}}
    @endforeach

    {{-- Cierra la última tabla al terminar el foreach --}}
    @if ($firstEl)
      </tbody>

      </table>
    @endif
    <div style="border-top: 1px solid black; margin: 15px 0;"></div>
  </div>
  <script type="text/php">
    if (isset($pdf)) {
      $x = 520;
      $y = 825;
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
