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
      margin-bottom: 5px;
      margin-top: 0px;

    }

    .title {
      font-size: 14px;
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
      margin-top: 140px;

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

    .content table {
      width: 100%;
      border: 1px solid black;
      border-collapse: collapse;
    }

    .content td {
      padding: 6px 8px;
      border: 1px solid black;
      vertical-align: top;
    }

    .title-row td {
      text-align: center;
      font-weight: bold;
      font-size: 16px;
      background-color: #f2f2f2;
      border: none;
      padding: 10px;
    }

    .label {
      font-weight: bold;
    }

    .section-separator {
      border-top: 1px solid black;
      margin: 15px 0;
    }

    .declaracion-container {
      font-family: Arial, sans-serif;
      font-size: 10px;
      line-height: 1.6;
      text-align: justify;
      padding: 10px;
    }

    .declaracion-container p {
      margin-bottom: 12px;
    }

    .declaracion-container ol {
      padding-left: 20px;
      margin-top: 10px;
      margin-bottom: 10px;
      line-height: 1.3;
      /* añadido */
    }

    .declaracion-container ol li {
      margin-bottom: 4px;
      /* antes 8px */
      line-height: 1.3;
      /* antes estaba heredando 1.6 */
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
          {{-- <span>Usuario: {{ $admin->nombres }}</span> --}}
        </td>
      </tr>
    </table>
    <div style="border-top: 2px solid black; margin: 5px 0;"></div>
    <table class="cuerpo-table">
      <tr class="title">
        <td><b>{{ $tipo }} {{ $periodo }}</b></td>
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
          <span style="font-size: 8px;">https://vrip.unmsm.edu.pe/convocatoria-2025/</span>
        </td>
        <td style="width: 10%; text-align: center;">
          {{-- <img src="data:image/png;base64,{{ $qr }}" alt="Código QR"
                        style="width: 30px; height: 30px;"> --}}
        </td>
      </tr>
    </table>
  </div>

  <div class="content">
    <table>
      <tr>
        <td colspan="2">
          Conste por el presente documento yo: <strong>{{ $proyecto->responsable }}</strong>
        </td>
      </tr>
      <tr>
        <td colspan="2">Profesor nombrado de la Facultad de: <strong>{{ $proyecto->facultad }}</strong></td>
      </tr>
      <tr>
        <td>Código Docente N°: <strong>{{ $proyecto->codigo_docente }}</strong></td>
        <td>Categoría: <strong>{{ $proyecto->categoria }}</strong></td>
      </tr>
      <tr>
        <td>DNI N°: <strong>{{ $proyecto->dni }}</strong></td>
        <td>Clase: <strong>{{ $proyecto->clase }}</strong></td>
      </tr>
      <tr>
        <td colspan="2">Grupo de investigación: <strong>{{ $proyecto->grupo_nombre }}</strong></td>
      </tr>
      <tr>
        <td colspan="2">
          En mi calidad de responsable del proyecto de investigación titulado:<br />
          <strong>{{ $proyecto->titulo_proyecto }}</strong>
        </td>
      </tr>
      <tr>
        <td>Código de proyecto: <strong>{{ $proyecto->codigo_proyecto }}</strong></td>
        <td>Monto asignado: S/. <strong>{{ number_format($proyecto->total_presupuesto, 2, '.', ',') }}</strong></td>

      </tr>
    </table>

    <div class="declaracion-container">
      <p>
        Declaro bajo juramento conocer las directivas, los procedimientos y el cronograma de ejecución de los
        Proyectos de Investigación 2025 y me comprometo a cumplirlos; en particular, declaro conocer los
        siguientes puntos:
      </p>

      <ol>
        <li>
          El Vicerrectorado de Investigación y Posgrado (VRIP) otorga la ASIGNACIÓN FINANCIERA DE
          INVESTIGACIÓN (AFI) 2025, de acuerdo a lo estipulado en la “Política de Financiamiento de la
          Investigación de la Universidad Nacional Mayor de San Marcos” (R.R. N.004801-2024-R/UNMSM).
        </li>
        <li>
          Los informes económicos se presentarán de acuerdo a la Directiva para la rendición económica de los
          fondos otorgados por la UNMSM para los proyectos de los programas de investigación del VRIP.
        </li>
        <li>
          El informe económico final, debidamente sustentado en original, deberá presentarse en el plazo
          establecido por el VRIP.
        </li>
        <li>
          Los recursos económicos de la AFI serán utilizados exclusivamente para los fines señalados en el
          plan de actividades y en el presupuesto aprobado por el VRIP a través del sistema RAIS. Su
          incumplimiento conlleva a la devolución del importe total asignado por la Universidad.
        </li>
        <li>
          En el caso de no rendir cuenta documentada en las fechas señaladas o, habiendo rendido, la Oficina
          de Control Previo y Fiscalización observe algunos documentos sustentatorios que no puedan ser
          subsanados, me comprometo a devolver el dinero recibido, más los intereses legales y otras acciones
          que correspondan a través de la Oficina de Asesoría Legal de la Universidad Nacional Mayor de San
          Marcos.
        </li>
        <li>
          Los equipos, instrumentos y libros que se adquieran (siguiendo lo establecido en la
          RD00366-DGA-2019: “Instructivo de manejo, control y ejecución de fondos asignados a proyectos de
          investigación para la adquisición de bienes de capital de la Universidad Nacional Mayor de San
          Marcos” y sus modificatorias) deberán ser entregados a la Facultad o dependencia correspondiente.
          Culminado el proyecto, los bienes deben permanecer en las instalaciones de la facultad (Instituto de
          Investigación, Unidad de Investigación, Grupos de Investigación o laboratorios).
        </li>
        <li>
          Si devuelvo dinero a la UNMSM por más del 10% del monto asignado, no podré participar en las
          actividades de investigación para el año 2026.
        </li>
      </ol>

      <p>
        En caso de incumplimiento de la presente declaración, el VRIP se reserva el derecho de cancelar el apoyo
        al proyecto, así como las subvenciones a los investigadores sin derecho a reintegro, de acuerdo con las
        normas vigentes.
      </p>

      <p>
        Por tanto, en mi calidad de responsable del citado proyecto de investigación, acepto haber leído y
        cumplir con las condiciones establecidas en la presente declaración para recibir asignación financiera
        al proyecto, así como asistir al taller de capacitación 2025. Habiendo aceptado las condiciones, doy
        conformidad y envío la declaración debidamente firmada vía sistema RAIS.
      </p>
    </div>

  </div>
  <table class="table-footer">
    <tr class="extra-firma">
      <td><strong>{{ $proyecto->responsable }} </strong><br><strong>DNI N°. {{ $proyecto->dni }}</strong></td>
    </tr>
  </table>

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
