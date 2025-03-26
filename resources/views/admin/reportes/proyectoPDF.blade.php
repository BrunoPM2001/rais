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
      margin-bottom: 10px;
    }

    .title {
      font-size: 18px;
      text-align: center;
      margin-top: 20px;
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

    .foot-1 {
      position: fixed;
      bottom: -20px;
      left: 0px;
      text-align: left;
      font-size: 10px;
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
  @php
    // Variables de control para evitar repeticiones
    $currentGroup = null; // Para controlar el grupo
    $currentProject = null; // Para controlar el proyecto
    $currentTipo = null; // Para controlar el "Condición" del proyecto
    $numProyecto = 1; // Contador de proyectos
    $firstEl = false; // Para saber si ya hemos impreso al menos un proyecto
    $numProyecto = 1;
  @endphp
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
        <span>Usuario: Omontes</span>
      </td>
    </tr>
  </table>

  <table class="cuerpo-table">
    <tr class="title">
      <td><b>{{ $tipo . ' ' . 'Año ' . $periodo }}
        </b><br></td>
    </tr>
  </table>

  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>
  <div class="cuerpo">
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
        {{-- Bloque con Área y Facultad (solo se imprime cuando cambia de grupo) --}}
        <table class="table-texto"
          style="width: 100%; border-bottom: 1px solid #e4e4e4; border-collapse: collapse; font-size:10px; margin-top: 15px;">
          <tr>
            <td style="text-align: left; width: 70%;">
              Área: {{ $item->sigla . ' ' . $item->area }}
            </td>
            <td style="text-align: right; width: 30%;">
              Facultad: {{ $item->facultad_grupo }}
            </td>
          </tr>
        </table>

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
              <strong>{{ $item->grupo_nombre . ' (' . strtoupper($item->grupo_nombre_corto) . ')' }}</strong>
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
              <td style="font-style: italic;"><b>{{ strtoupper($item->titulo) }}</b></td>
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
              <th style="width: 45%; text-align: center;">Apellidos y nombres</th>
              <th style="width: 20%;">Tipo</th>
              <th style="width: 30%;">Facultad</th>
              <th style="width: 17%;">Condición en GI</th>
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
        <td style="font-size:8px;">{{ $item->nombres }}</td>
        <td>{{ $item->condicion }}</td>
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
      $x = 527;
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

</html>
