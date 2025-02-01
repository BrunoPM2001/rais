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

    .desc {
      font-size: 11px;
    }
  </style>
</head>

<body>

  <h6>Resumen ejecutivo</h6>
  <div class="desc">
    @if (isset($detalles['estado_arte']))
      {!! $detalles['resumen_ejecutivo'] !!}
    @endif
  </div>

  <h6>Palabras clave</h6>
  <div class="desc">
    {{ $proyecto->palabras_clave }}
  </div>

  <h6>Estado del arte</h6>
  <div class="desc">
    @if (isset($detalles['estado_arte']))
      {!! $detalles['estado_arte'] !!}
    @endif
  </div>

  <h6>Planteamiento del problema</h6>
  <div class="desc">
    @if (isset($detalles['planteamiento_problema']))
      {!! $detalles['planteamiento_problema'] !!}
    @endif
  </div>

  <h6>Justificación</h6>
  <div class="desc">
    @if (isset($detalles['justificacion']))
      {!! $detalles['justificacion'] !!}
    @endif
  </div>

  <h6>Contribución e impacto</h6>
  <div class="desc">
    @if (isset($detalles['contribucion_impacto']))
      {!! $detalles['contribucion_impacto'] !!}
    @endif
  </div>

  <h6>Objetivos</h6>
  <div class="desc">
    @if (isset($detalles['objetivos']))
      {!! $detalles['objetivos'] !!}
    @endif
  </div>

  <h6>Metodología de trabajo</h6>
  <div class="desc">
    @if (isset($detalles['metodologia_trabajo']))
      {!! $detalles['metodologia_trabajo'] !!}
    @endif
  </div>

</body>
