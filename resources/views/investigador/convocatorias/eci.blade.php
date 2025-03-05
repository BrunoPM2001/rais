@php
  $currentFacultad = '';
  $counter = 0;
  $totalSum = [];
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
      font-family: Helvetica;
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
      EQUIPAMIENTO CIENTÍFICO PARA LA INVESTIGACIÓN {{ $proyecto->periodo }}
    </strong>
  </p>

  <div class="cuerpo">

    <h5>I. Información del grupo de investigación::</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Grupo</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->grupo_nombre }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Responsable del proyecto</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->responsable }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Área académica</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->area }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Facultad</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Categoría</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $grupo->categoria }}</td>
        </tr>
      </tbody>
    </table>

    <h5>II. Datos generales:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Título de la propuesta</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->titulo }}</td>
        </tr>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Línea de investigación</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->linea }}</td>
        </tr>       
      </tbody>
    </table>

    <h5>III. Resumen:</h5>
    <div class="desc">
      @if (isset($detalles['resumen']))
        {!! $detalles['resumen'] !!}
      @endif
    </div>

    <h5>IV. Justificación de la propuesta</h5>
    <div class="desc">
      @if (isset($detalles['justificacion']))
        {!! $detalles['justificacion'] !!}
      @endif
    </div>

    <h5>V. Propuesta de equipamiento científico</h5>
    <div class="desc">
      @if (isset($detalles['propuesta']))
        {!! $detalles['propuesta'] !!}
      @endif
    </div>

    <h5>VI. Equipamiento:</h5>

    <h6>Nombre del equipo</h6>
    <div class="desc">
      @if (isset($detalles['nombre_equipo']))
        {!! $detalles['nombre_equipo'] !!}
      @endif
    </div>

    <h6>Descripción del equipo</h6>
    <div class="desc">
      @if (isset($detalles['desc_equipo']))
        {!! $detalles['desc_equipo'] !!}
      @endif
    </div>

    <h5>VII. Especificaciones técnicas y cotizaciones:</h5>

    <table class="table">
      <thead>
        <tr>
          <th>Tipo de documento</th>
          <th>Comentario</th>
          <th>Archivo</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($docs1) > 0)
          @foreach ($docs1 as $item)
            <tr>
              <td>{{ $item->nombre }}</td>
              <td>{{ $item->comentario }}</td>
              <td>Sí</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="3">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>VIII. Presupuesto:</h5>
    <table class="table">
      <thead>
        <tr>
          <th>Nro.</th>
          <th>Partida</th>
          <th>Tipo</th>
          <th>Monto</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($presupuesto) > 0)
          @foreach ($presupuesto as $item)
            <tr>
              <td align="center">{{ $loop->iteration }}</td>
              <td>{{ $item->partida }}</td>
              <td>{{ $item->tipo }}</td>
              <td>{{ $item->monto }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="4">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>IX. Impacto de la propuesta:</h5>

    <h6>Impacto</h6>
    <div class="desc">
      @if (isset($detalles['impacto_propuesta']))
        {!! $detalles['impacto_propuesta'] !!}
      @endif
    </div>

    <h5>X. Documentos obligatorios:</h5>

    <table class="table">
      <thead>
        <tr>
          <th>Tipo de documento</th>
          <th>Comentario</th>
          <th>Archivo</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($docs2) > 0)
          @foreach ($docs2 as $item)
            <tr>
              <td>{{ $item->nombre }}</td>
              <td>{{ $item->comentario }}</td>
              <td>Sí</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="3">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>XI. Administración de equipamiento solicitado:</h5>

    <h6>Plan de manejo de residuos/efluentes o emisiones si corresponde</h6>
    <div class="desc">
      @if (isset($detalles['plan_manejo']))
        {!! $detalles['plan_manejo'] !!}
      @endif
    </div>

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