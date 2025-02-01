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

  <h6>Resumen ejecutivo</h6>
  <div class="desc">
    {!! $detalles['resumen_ejecutivo'] !!}
  </div>

  <h6>Palabras clave</h6>
  <div class="desc">
    {{ $proyecto->palabras_clave }}
  </div>

  <h6>Antecedentes</h6>
  <div class="desc">
    {!! $detalles['antecedentes'] !!}
  </div>

  <h6>Objetivos generales</h6>
  <div class="desc">
    {!! $detalles['objetivos_generales'] !!}
  </div>

  <h6>Objetivos específicos</h6>
  <div class="desc">
    {!! $detalles['objetivos_especificos'] !!}
  </div>

  <h6>Justificación</h6>
  <div class="desc">
    {!! $detalles['justificacion'] !!}
  </div>

  <h6>Hipótesis</h6>
  <div class="desc">
    {!! $detalles['hipotesis'] !!}
  </div>

  <h6>Metodología de trabajo</h6>
  <div class="desc">
    {!! $detalles['metodologia_trabajo'] !!}
  </div>

  <h6>Resultados esperados</h6>
  <div class="desc">
    @if (isset($detalles['resumen_esperado']))
      {!! $detalles['resumen_esperado'] !!}
    @endif
  </div>
</body>
