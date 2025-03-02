@php
  use Carbon\Carbon;
  $total = 0;
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

    @page {
      margin: 145px 40px 35px 40px;
    }

    .head-1 {
      position: fixed;
      top: -115px;
      left: 0px;
      height: 90px;
    }

    .head-1 img {
      margin-left: 120px;
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

    .foot-1 {
      position: fixed;
      bottom: -15px;
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

    .table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000;
      margin-bottom: 30px;
    }

    .table>thead th {
      font-size: 10px;
      border: 1.5px solid #000;
      padding: 5px 3px 6px 3px;
      font-weight: bold;
    }

    .table>tbody td {
      font-size: 11px;
      border: 1.5px solid #000;
      padding: 5px 3px 6px 3px;
    }

    .tableData {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .tableData>tbody td {
      font-size: 11px;
      padding: 5px 3px 6px 3px;
    }

    .desc {
      font-size: 11px;
    }

    .subtitulo {
      font-size: 14px;
      text-align: center;
    }
    .obs {
      background-color: #ff9a9a;
      border-radius: 2px;
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
      padding: 2px 4px;
    }

    .obs>tbody td {
      font-size: 11px;
      padding: 5px 3px 6px 3px;
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
  </div>
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>
  <p class="titulo">
    <strong>
      Proyecto de tesis de pregrado {{ $proyecto->periodo }}
    </strong>
  </p>

  <p class="subtitulo">
    <strong>
      Estado: {{ $proyecto->estado }} {{ Carbon::parse($proyecto->updated_at)->format('d/m/Y') }}
    </strong>
  </p>

  @if ($proyecto->estado == "Observado")
    <table class="obs">
      <tbody>
        <tr>
          <td style="width: 12%;" valign="top"><strong>Observaciones</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 87%;" valign="top">{{ $proyecto->observaciones_admin }}</td>
        </tr>
      </tbody>
    </table>
  @endif

  <div class="cuerpo">

    <h5>I. Datos del asesor:</h5>

    <h6>Datos personales:</h6>
    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Nombres y apellidos</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->nombres }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>N° de documento</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->doc_numero }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Fecha de nacimiento</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->fecha_nac }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Código docente</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->codigo }}</td>
        </tr>
      </tbody>
    </table>

    <h6>Datos profesionales:</h6>
    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Categoría y clase</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->docente_categoria }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Código ORCID</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->codigo_orcid }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Regina</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->regina }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Dina</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->dina }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Google Scholar</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->google_scholar }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Grupo de investigación</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $responsable->grupo_nombre }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Carta de compromiso del asesor</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">
            @if ($responsable->archivo)
              Enviado
            @else
              No enviado
            @endif
          </td>
        </tr>
      </tbody>
    </table>

    <h5>II. Datos de postulación - tesista docente:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Nombres</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $tesista->nombres }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Tipo de integrante</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $tesista->tipo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Facultad</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $tesista->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Carta de compromiso del tesista estudiante</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">
            @if ($tesista->archivo)
              Enviado
            @else
              No enviado
            @endif
          </td>
        </tr>
      </tbody>
    </table>

    <h5>III. Datos generales:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Título del proyecto de tesis</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Línea de investigación</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->linea }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Línea OCDE</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->ocde }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Lugar de ejecución</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->localizacion }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Registro de evaluación de la tesis en la UI</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">
            @if ($proyecto->archivo)
              Enviado
            @else
              No enviado
            @endif
          </td>
        </tr>
      </tbody>
    </table>

    <h5>IV. Descripción del proyecto:</h5>

    <h6>Resumen ejecutivo:</h6>
    <div class="desc">
      @if (isset($descripcion['resumen_ejecutivo']))
        {!! $descripcion['resumen_ejecutivo'] !!}
      @endif
    </div>

    <h6>Planteamiento del problema:</h6>
    <div class="desc">
      @if (isset($descripcion['planteamiento_problema']))
        {!! $descripcion['planteamiento_problema'] !!}
      @endif
    </div>

    <h6>Hipótesis:</h6>
    <div class="desc">
      @if (isset($descripcion['hipotesis']))
        {!! $descripcion['hipotesis'] !!}
      @endif
    </div>

    <h6>Justificación:</h6>
    <div class="desc">
      @if (isset($descripcion['justificacion']))
        {!! $descripcion['justificacion'] !!}
      @endif
    </div>

    <h6>Antecedentes:</h6>
    <div class="desc">
      @if (isset($descripcion['antecedentes']))
        {!! $descripcion['antecedentes'] !!}
      @endif
    </div>

    <h6>Objetivos generales:</h6>
    <div class="desc">
      @if (isset($descripcion['objetivos_generales']))
        {!! $descripcion['objetivos_generales'] !!}
      @endif
    </div>

    <h6>Objetivos específicos:</h6>
    <div class="desc">
      @if (isset($descripcion['objetivos_especificos']))
        {!! $descripcion['objetivos_especificos'] !!}
      @endif
    </div>

    <h6>Metodología:</h6>
    <div class="desc">
      @if (isset($descripcion['metodologia_trabajo']))
        {!! $descripcion['metodologia_trabajo'] !!}
      @endif
    </div>

    <h6>Referencias bibliográficas:</h6>
    <div class="desc">
      @if (isset($descripcion['referencias_bibliograficas']))
        {!! $descripcion['referencias_bibliograficas'] !!}
      @endif
    </div>

    <h6>Anexo:</h6>
    <div class="desc">
      @if ($proyecto->anexo)
        Enviado
      @else
        No enviado
      @endif
    </div>

    <h5>V. Cronograma de actividades:</h5>
    <table class="table">
      <thead>
        <tr>
          <th></th>
          <th>Actividad</th>
          <th>Fecha inicial</th>
          <th>Fecha final</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($actividades) > 0)
          @foreach ($actividades as $act)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $act->actividad }}</td>
              <td>{{ $act->fecha_inicio }}</td>
              <td>{{ $act->fecha_fin }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="4" align="center">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>VI. Financiamiento:</h5>
    <table class="table">
      <thead>
        <tr>
          <th></th>
          <th>Código</th>
          <th>Partida</th>
          <th>Tipo</th>
          <th>Monto S/.</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($presupuesto) > 0)
          @foreach ($presupuesto as $pre)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $pre->codigo }}</td>
              <td>{{ $pre->partida }}</td>
              <td>{{ $pre->tipo }}</td>
              <td>{{ $pre->monto }}</td>
            </tr>
            @php
              $total += $pre->monto;
            @endphp
          @endforeach
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>Total</td>
            <td>{{ $total }}</td>
          </tr>
        @else
          <tr>
            <td colspan="4" align="center">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>VII. Justificación de la solicitud de financiamiento:</h5>
    <div class="desc">
      @if (isset($descripcion['presupuesto_justificacion']))
        {!! $descripcion['presupuesto_justificacion'] !!}
      @endif
    </div>

    <h5>VIII. Otras fuentes de financiamiento:</h5>
    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Monto en S/</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">
              {{ $descripcion['presupuesto_otros_fondo_monto'] }}
          </td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Fuente</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">
              {{ $descripcion['presupuesto_otros_fondo_fuente'] }} 
          </td>
        </tr>
      </tbody>
    </table>

  </div>

  <script type="text/php">
    if (isset($pdf)) {
      $x = 515;
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
