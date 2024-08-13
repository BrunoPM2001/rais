@php
  use Carbon\Carbon;
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
      margin: 130px 40px 20px 40px;
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

    .head-2 .user {
      font-size: 10px;
      margin-top: 0;
    }

    .foot-1 {
      position: fixed;
      bottom: 0px;
      left: 0px;
      text-align: left;
      font-size: 11px;
      font-style: oblique;
    }

    .titulo {
      border-top: 1px solid #000;
      border-bottom: 1px solid #000;
      padding-top: 5px;
      padding-bottom: 5px;
      font-size: 16px;
      text-align: center;
    }
    
    .subtitulo {
      font-size: 14px;
      text-align: center;
    }

    .subtitulo-1 {
      font-size: 12px;
      text-align: center;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .table>thead th {
      font-size: 10px;
      font-weight: bold;
      border-top: 1.5px solid #000;
      border-bottom: 1.5px solid #000;
    }

    .table>tbody td {
      font-size: 10px;
      padding: 5px 3px 6px 3px;
      border-bottom: 1px dashed #000;
    }

    .cuerpo>p {
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
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <p class="titulo"><strong>Registro de libro publicado con ISBN</strong></p>

  <p class="subtitulo">
    <strong>
    Estado: 
      @switch($publicacion->estado)
        @case(-1)
          Eliminado
        @break

        @case(1)
          Registrado
        @break

        @case(2)
          Observado
        @break

        @case(5)
          Enviado
        @break

        @case(6)
          En proceso
        @break

        @case(7)
          Anulado
        @break

        @case(8)
          No registrado
        @break

        @case(9)
          Duplicado
        @break

        @default
          Sin estado
      @endswitch
       {{ Carbon::parse($publicacion->updated_at)->format("d/m/Y") }}
    </strong>
  </p>

  @if ($publicacion->categoria != null)
  <p class="subtitulo-1">
    <strong>
    {{ $publicacion->categoria }}
    </strong>
  </p>
  @endif

  <div class="cuerpo">

    <h5>I. Descripción de la Publicación:</h5>

    <p>
      <b>Código:</b>
      {{ $publicacion->codigo_registro == null ? 'No tiene código' : $publicacion->codigo_registro }}
    </p>
    <p>
      <b>Isbn: </b>
      {{ $publicacion->isbn }}
    </p>
    <p>
      <b>Título del libro: </b>
      {{ $publicacion->titulo }}
    </p>
    <p>
      <b>Editorial: </b>
      {{ $publicacion->editorial }}
    </p>
    <p>
      <b>Ciudad: </b>
      {{ $publicacion->lugar_publicacion }}
    </p>
    <p>
      <b>Edición: </b>
      {{ $publicacion->edicion }}
    </p>
    <p>
      <b>Volumen / Tomo: </b>
      {{ $publicacion->volumen }}
    </p>
    <p>
      <b>N° total de pág (Libro): </b>
      {{ $publicacion->pagina_total }}
    </p>
    <p>
      <b>Fecha de publicación: </b>
      {{ $publicacion->fecha_publicacion }}
    </p>
    <p>
      <b>Palabras clave: </b>
      {{ $palabras_clave }}
    </p>
    <p>
      <b>Url de la publicación: </b>
      {{ $publicacion->url }}
    </p>
    <p>
      <b>País: </b>
      {{ $publicacion->pais }}
    </p>

    <h5>II. Autores:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 25%;" align="left">Autor</th>
          <th style="width: 10%;" align="left">Tipo de integrante</th>
          <th style="width: 25%;" align="left">Profesor San Marcos</th>
          <th style="width: 10%;" align="left">Filiación UNMSM</th>
          <th style="width: 10%;" align="left">Fecha</th>
          <th style="width: 10%;" align="left">N° registro</th>
          <th style="width: 10%;" align="left">Puntaje</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($autores) == 0)
          <tr>
            <td colspan="4" align="center">
              No hay autores registrados
            </td>
          </tr>
        @endif
        @foreach ($autores as $autor)
          <tr>
            <td>{{ $autor->autor }}</td>
            <td>{{ $autor->categoria }}</td>
            <td>{{ $autor->nombres }}</td>
            <td>{{ $autor->filiacion }}</td>
            <td>{{ Carbon::parse($autor->created_at)->format("Y-m-d") }}</td>
            <td>{{ $autor->nro_registro }}</td>
            <td>{{ $autor->puntaje }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h5>III. Proyecto de investigación financiado por:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 15%;" align="left">Código de proyecto</th>
          <th style="width: 70%;" align="left">Título</th>
          <th style="width: 15%;" align="left">Entidad financiadora</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($proyectos) == 0)
          <tr>
            <td colspan="3" align="center">
              No hay proyectos registrados
            </td>
          </tr>
        @endif
        @foreach ($proyectos as $proyecto)
          <tr>
            <td>{{ $proyecto->codigo_proyecto }}</td>
            <td>{{ $proyecto->nombre_proyecto }}</td>
            <td>{{ $proyecto->entidad_financiadora }}</td>
          </tr>
        @endforeach
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
