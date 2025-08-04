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
  </style>
</head>

<body>
  <<h5 class="section-title">Título del proyecto:</h5>
  <div class="text">{{ $proyecto->titulo }}</div>

  <h5 class="section-title">Código del proyecto:</h5>
  <div class="text">{{ $proyecto->codigo_proyecto }}</div>

    {{-- AUTORES / INTEGRANTES --}}
  <h5 class="section-title">Autores:</h5>
  <div class="text">
    <ul>
      @foreach ($integrantes as $i)
        <li>{{ $i->nombres }} ({{ $i->condicion }})</li>
      @endforeach
    </ul>
  </div>

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

</body>
