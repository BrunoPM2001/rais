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
            text-align: left;
        }

        .table-footer {
            width: 100%;
            text-align: center;
            margin-top: 90px;

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
            border-bottom: 1px solid black;
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

        .table1 {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .table1 th,
        .table1 td {
            padding: 8px;
            text-align: left;
        }

        .table1 th {
            border-bottom: 2px solid black;
            /* Línea gruesa para el encabezado */
        }

        .table1 td {
            border-bottom: 1px solid black;
            /* Línea delgada para filas */
        }

        .table1 tbody tr:last-child td {
            border-bottom: none;
            /* Quita la línea de la última fila */
        }

        .table1 th:first-child,
        .table1 td:first-child {
            text-align: center;
            /* Centra los números en la primera columna */
        }

        .desc {
            font-size: 11px;
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
            <td><b>Programa para la Inducción en Investigación Científica en el <br>
                    Verano 2025 (PICV-UNMSM) </b>
            </td>

        </tr>
        <tr>
            <td style="font-size:14px;"><b>Estado :</b>{{ $proyecto->estado ?? '' }}</td>
        </tr>
        <tr>
            <td style="font-size:14px;"><b>Fecha :</b>{{ $proyecto->updated_at ?? '' }}</td>
        </tr>
    </table>
    <table style="width: 100%;">
        <tbody>
            <tr>
                <td colspan="100%" style="margin: 0;padding: 0;">
                    <h5
                        style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px; ">
                        I. Información General
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="font-size:12px; ">
        <tr>
            <td>Codigo Proyecto :</td>
            <td> <strong>{{ $proyecto->codigo_proyecto ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>Titulo del Proyecto :</td>
            <td><strong>{{ $proyecto->titulo ?? '-' }}</strong></td>
        </tr>

        <tr>
            <td>Grupo de investigación :</td>

            <td><strong>{{ $proyecto->grupo_nombre ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>Facultad :</td>

            <td><strong>{{ $proyecto->facultad_nombre ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>Área académica:</td>
            <td><strong>{{ $proyecto->area_nombre ?? '-' }}</strong></td>
        </tr>

        <tr>
            <td>Linea de Investigación:</td>
            <td><strong>{{ $proyecto->linea_nombre ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>ODS :</td>
            <td> <strong>{{ $proyecto->objetivo ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>línea OCDE :</td>
            <td> <strong>{{ $proyecto->linea ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>Localización :</td>
            <td> <strong>{{ $proyecto->localizacion ?? '-' }}</strong></td>
        </tr>
        @foreach ($descripcion as $des)
            @switch($des->codigo)
                @case('objetivo_ods')
                    <tr>
                        <td>Objetivo ODS :</td>
                        <td> <strong>{{ $des->detalle ?? '-' }}</strong></td>
                    </tr>
                @break

                @case('tipo_investigacion')
                    <tr>
                        <td>Tipo de Investigación :</td>
                        <td> <strong>{{ $des->detalle ?? '-' }}</strong></td>
                    </tr>
                @break

                @default
            @endswitch
        @endforeach
    </table>

    <table style="width: 100%;">
        <tbody>
            <tr>
                <td colspan="100%" style="margin: 0;padding: 0;">
                    <h5
                        style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px; ">
                        II. Descripción del Proyecto
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>
    @foreach ($descripcion as $des)
        @switch($des->codigo)
            @case('resumen_ejecutivo')
                <h5>Resumen Ejecutivo</h5>
            @break

            @case('antecedentes')
                <h5>Antecedentes</h5>
            @break

            @case('justificacion')
                <h5>Justificación</h5>
            @break

            @case('contribucion_impacto')
                <h5>Contribucion e impacto</h5>
            @break

            @case('hipotesis')
                <h5>Hipótesis</h5>
            @break

            @case('objetivos')
                <h5>Objetivos</h5>
            @break

            @case('metodologia_trabajo')
                <h5>Metodología</h5>
            @break

            @case('referencias_bibliograficas')
                <h5>Referencias Bibliográficas</h5>
            @break

            @default
        @endswitch
        @if (!in_array($des->codigo, ['objetivo_ods', 'tipo_investigacion']))
            <div class="desc">{!! $des->detalle !!}</div>
        @endif
    @endforeach

    <table style="width: 100%;">
        <tbody>
            <tr>
                <td colspan="100%" style="margin: 0;padding: 0;">
                    <h5
                        style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px; ">
                        III. Calendario de Actividades
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table1">
        <thead>
            <tr>
                <th>Nro.</th>
                <th>Actividad</th>
                <th>Fecha inicial</th>
                <th>Fecha final</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($actividades as $act)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $act->actividad }}</td>
                    <td>{{ $act->fecha_inicio }}</td>
                    <td>{{ $act->fecha_fin }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table style="width: 100%;">
        <tbody>
            <tr>
                <td colspan="100%" style="margin: 0;padding: 0;">
                    <h5
                        style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px; ">
                        IV. Integrantes del Proyecto
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table1">
        <thead>
            <tr>
                <th>Nro</th>
                <th>Condición</th>
                <th>Apellidos y Nombres </th>
                <th>Tipo</th>
                <th>Facultad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($integrantes as $int)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $int->condicion }}</td>
                    <td>{{ $int->integrante }}</td>
                    <td>{{ $int->tipo }}</td>
                    <td>{{ $int->facultad }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width:100%; font-size:10px;font-style:italic;margin-top:25px; text-align:justify;">
        <tr>
            <td>Los docentes y/o estudiantes deben indicar, en cada publicación o forma de divulgación (tesis,
                artículos,
                libros, resúmenes de trabajos presentados en congresos, páginas de
                internet y cualquier otra publicación) que resulten del apoyo de la UNMSM, el siguiente párrafo:</td>
        </tr>
        <tr>
            <td> Para los artículos escritos en español: </td>
        </tr>
        <tr>
            <td>
                Esta investigación fue financiada por la Universidad Nacional Mayor de San Marcos – RR N° aabb-cc con
                código
                de proyecto dfgh.
            </td>
        </tr>
        <tr>
            <td>
                Para los artículos escritos en algún idioma extranjero, indicar el apoyo de la UNMSM en inglés:
            </td>
        </tr>
        <tr>
            <td>
                This research was supported by the Universidad Nacional Mayor de San Marcos – RR N° aabb-cc and project
                number dfgh.
            </td>

        </tr>
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

</html>
