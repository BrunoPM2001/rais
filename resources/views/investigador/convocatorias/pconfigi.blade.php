@php
    use Carbon\Carbon;
    $total = 0.0;
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
            <td><b>Programa de Proyectos de Investigación para Grupos de <br>
                    Investigación (PCONFIGI) {{ $proyecto->periodo }} </b>
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
                        I. Información del grupo de investigación:
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="font-size:12px;">
        <tbody>
            <tr>
                <td><strong>Grupo de investigación</strong></td>
                <td>{{ $proyecto->grupo_nombre }}</td>
            </tr>
            <tr>
                <td><strong>Facultad</strong></td>
                <td>{{ $proyecto->facultad }}</td>
            </tr>
            <tr>
                <td><strong>Área académica</strong></td>
                <td>{{ $proyecto->area }}</td>
            </tr>
            <tr>
                <td><strong>Tipo de investigación</strong></td>
                <td>
                    @switch($detalles["tipo_investigacion"] ?? "")
                        @case('basica')
                            Básica (aumento del conocimiento existente sobre el tema)
                        @break

                        @case('aplicada')
                            Aplicada (utilización del conocimiento existente para mejorar algo)
                        @break

                        @case('exploratoria')
                            Exploratoria (examinar un problema poco estudiado o no analizado antes)
                        @break

                        @case('experimental')
                            Experimental (explicar el contenido del problema o fenómeno que se investiga)
                        @break

                        @case('teorica')
                            Teórica (estudios filosóficos, jurídicos, culturales)
                        @break

                        @case('otro')
                            Otros
                        @break

                        @default
                    @endswitch
                </td>
            </tr>
            <tr>
                <td><strong>Línea OCDE</strong></td>
                <td>{{ $proyecto->ocde }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width: 100%;">
        <tbody>
            <tr>
                <td colspan="100%" style="margin: 0;padding: 0;">
                    <h5
                        style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px; ">
                        II. Información del proyecto:
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="font-size:12px; ">
        <tbody>
            <tr>
                <td><strong>Código de proyecto :</strong></td>
                <td>{{ $proyecto->codigo_proyecto }}</td>
            </tr>
            <tr>
                <td><strong>Título :</strong></td>
                <td>{{ $proyecto->titulo }}</td>
            </tr>
            <tr>
                <td><strong>Línea de investigación :</strong></td>
                <td>{{ $proyecto->linea }}</td>
            </tr>
            <tr>
                <td><strong>Objetivo de Desarrollo Sostenible (ODS) :</strong></td>
                <td>{{ $detalles['objetivo_ods'] ?? '' }}</td>
            </tr>
            <tr>
                <td><strong>Localización : </strong></td>
                <td>{{ $proyecto->localizacion }}</td>
            </tr>
        </tbody>
    </table>
    <table style="width: 100%;">
        <tbody>
            <tr>
                <td colspan="100%" style="margin: 0;padding: 0;">
                    <h5
                        style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px; ">
                        III. Descripción del proyecto:
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>
    <h5>Resumen ejecutivo:</h5>
    <div style="font-size: 11px; text-align: justify;">{!! $detalles['resumen_ejecutivo'] !!}</div>

    <h5>Palabras clave:</h5>
    <div style="font-size: 11px; text-align: justify;">
        {{ $proyecto->palabras_clave }}
    </div>

    <h5>Antecedentes:</h5>
    <div style="font-size: 11px; text-align: justify;">
        {!! $detalles['antecedentes'] !!}
    </div>

    <h5>Justificación:</h5>
    <div style="font-size: 11px; text-align: justify;">
        {!! $detalles['justificacion'] !!}
    </div>

    <h5>Contribución e impacto:</h5>
    <div style="font-size: 11px; text-align: justify;">
        {!! $detalles['contribucion_impacto'] !!}
    </div>

    <h5>Hipótesis:</h5>
    <div style="font-size: 11px; text-align: justify;">
        {!! $detalles['hipotesis'] !!}
    </div>

    <h5>Objetivos:</h5>
    <div style="font-size: 11px; text-align: justify;">
        {!! $detalles['objetivos'] !!}
    </div>

    <h5>Metodología de trabajo:</h5>
    <div style="font-size: 11px; text-align: justify;">
        {!! $detalles['metodologia_trabajo'] !!}
    </div>

    <h5>Referencias bibliográficas:</h5>
    <div style="font-size: 11px; text-align: justify;">
        {!! $detalles['referencias_bibliograficas'] !!}
    </div>
    <table style="width: 100%;">
        <tbody>
            <tr>
                <td colspan="100%" style="margin: 0;padding: 0;">
                    <h5
                        style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px; ">
                        IV. Calendario de Actividades
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table1">
        <thead>
            <tr>
                <th>N°</th>
                <th>Actividad</th>
                <th>Fecha inicial</th>
                <th>Fecha final</th>
            </tr>
        </thead>
        <tbody>
            @if (sizeof($calendario) == 0)
                <tr>
                    <td colspan="4" align="center">
                        No hay actividades registradas
                    </td>
                </tr>
            @endif
            @foreach ($calendario as $item)
                <tr>
                    <td style="text-align: center">{{ $loop->iteration }}</td>
                    <td>{{ $item->actividad }}</td>
                    <td>{{ $item->fecha_inicio }}</td>
                    <td>{{ $item->fecha_fin }}</td>
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
                        V. Presupuesto
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table1">
        <thead>
            <tr>
                <th>Nro</th>
                <th>Partida</th>
                <th>Justificación</th>
                <th>Tipo</th>
                <th>Monto S/.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($presupuesto as $pres)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $pres->partida }}</td>
                    <td>{{ $pres->justificacion }}</td>
                    <td>{{ $pres->tipo }}</td>
                    <td>{{ $pres->monto }}</td>
                </tr>
                @php
                    $total += $pres->monto;
                @endphp
            @endforeach
            <tr>
                <td colspan="4">Total</td>
                <td>{{ $total }}</td>
            </tr>
        </tbody>
    </table>
    <table style="width: 100%;">
        <tbody>
            <tr>
                <td colspan="100%" style="margin: 0;padding: 0;">
                    <h5
                        style="width: 100%; padding: 10px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 10px; ">
                        VI. Integrantes
                    </h5>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table1">
        <thead>
            <tr>
                <th style="width: 10%;">Condición</th>
                <th style="width: 25%;">Apellidos y nombres</th>
                <th style="width: 20%;">Tipo</th>
                <th style="width: 20%;">Tipo de tesis</th>
                <th style="width: 25%;">Título de la tesis</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($integrantes as $item)
                <tr>
                    <td>{{ $item->condicion }}</td>
                    <td>{{ $item->nombres }}</td>
                    <td>{{ $item->tipo }}</td>
                    <td>{{ $item->tipo_tesis }}</td>
                    <td>{{ $item->titulo_tesis }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h5>VII. Condiciones:</h5>
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

</body>
