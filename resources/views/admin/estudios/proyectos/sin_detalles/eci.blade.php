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
  <h5>Resumen:</h5>
  <div style="font-size: 11px; text-align: justify;">{!! $detalles['resumen'] !!}</div>

  <h5>Propuesta:</h5>
  <div style="font-size: 11px; text-align: justify;">{!! $detalles['propuesta'] !!}</div>

  <h5>Justificación:</h5>
  <div style="font-size: 11px; text-align: justify;">{!! $detalles['justificacion'] !!}</div>

  <h5>Impacto:</h5>
  <div style="font-size: 11px; text-align: justify;">{!! $detalles['impacto_propuesta'] !!}</div>

  <h5>Nombre del equipo:</h5>
  <div style="font-size: 11px; text-align: justify;">{!! $detalles['nombre_equipo'] !!}</div>

  <h5>Descripción del equipo:</h5>
  <div style="font-size: 11px; text-align: justify;">{!! $detalles['desc_equipo'] !!}</div>

  <h5>Plan de manejo:</h5>
  <div style="font-size: 11px; text-align: justify;">{!! $detalles['plan_manejo'] !!}</div>

</body>
