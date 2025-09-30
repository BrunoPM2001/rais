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

    .desc {
      font-size: 11px;
    }
  </style>
</head>

<body>

  <div class="cuerpo">

    <h5>I. Proyecto:</h5>
    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 24%;" valign="top"><strong>Título</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 75%;" valign="top">{{ $proyecto->titulo }}</td>
        </tr>

      </tbody>
    </table>

    <h5>II. Integrantes del proyecto:</h5>
    <table class="table">
      <thead>
        <tr>
          <th>Nro.</th>
          <th>Nombres </th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($integrantes) > 0)
          @foreach ($integrantes as $int)
            <tr>
              <td align="center">{{ $loop->iteration }}</td>
              <td>{{ $int->integrante }}</td>
              <td>( {{ $int->condicion_proyecto }} )</td>

            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="4">No hay registros</td>
          </tr>
        @endif
      </tbody>
    </table>

    <h5>III. Detalles del proyecto:</h5>
    <h6>Resumen ejecutivo</h6>
    <div class="desc">
      @if (isset($descripcion['resumen_ejecutivo']))
        {!! $descripcion['resumen_ejecutivo'] !!}
      @endif
    </div>

    <h6>Palabras clave</h6>
    <div class="desc">
      {{ $proyecto->palabras_clave }}
    </div>

    <h6>Antecedentes</h6>
    <div class="desc">
      @if (isset($descripcion['antecedentes']))
        {!! $descripcion['antecedentes'] !!}
      @endif
    </div>

    <h6>Justificación</h6>
    <div class="desc">
      @if (isset($descripcion['justificacion']))
        {!! $descripcion['justificacion'] !!}
      @endif
    </div>

    <h6>Contribución e impacto</h6>
    <div class="desc">
      @if (isset($descripcion['contribucion_impacto']))
        {!! $descripcion['contribucion_impacto'] !!}
      @endif
    </div>

    <h6>Hipótesis</h6>
    <div class="desc">
      @if (isset($descripcion['hipotesis']))
        {!! $descripcion['hipotesis'] !!}
      @endif
    </div>

    <h6>Objetivos</h6>
    <div class="desc">
      @if (isset($descripcion['objetivos']))
        {!! $descripcion['objetivos'] !!}
      @endif
    </div>

    <h6>Metodología de trabajo</h6>
    <div class="desc">
      @if (isset($descripcion['metodologia_trabajo']))
        {!! $descripcion['metodologia_trabajo'] !!}
      @endif
    </div>

    <h6>Referencias bibliográficas</h6>
    <div class="desc">
      @if (isset($descripcion['referencias_bibliograficas']))
        {!! $descripcion['referencias_bibliograficas'] !!}
      @endif
    </div>
  </div>
</body>
