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

    .desc {
      font-size: 11px;
    }
  </style>

</head>


<body>

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

      @default
    @endswitch
    @if (!in_array($des->codigo, ['objetivo_ods', 'tipo_investigacion', 'referencias_bibliograficas']))
      <div class="desc">{!! $des->detalle !!}</div>
    @endif
  @endforeach

</body>

</html>
