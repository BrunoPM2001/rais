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
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <p class="titulo"><strong>Propiedad intelectual</strong></p>

  <p class="subtitulo">
    <strong>
    Estado: 
      @switch($patente->estado)
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
       {{ Carbon::parse($patente->updated_at)->format("d/m/Y") }}
    </strong>
  </p>

  @if ($patente->estado == 2)
    <table class="obs">
      <tbody>
        <tr>
          <td style="width: 12%;" valign="top"><strong>Observaciones</strong></td>
          <td style="width: 1%;" valign="top">:</td>
          <td style="width: 87%;" valign="top">{{ $patente->observaciones_usuario }}</td>
        </tr>
      </tbody>
    </table>
  @endif
  
  <div class="cuerpo">

    <h5>I. Descripción de la propiedad intelectual:</h5>

    <p>
      <b>1.1 Número de registro:</b>
      {{ $patente->nro_registro == null ? 'No tiene n°' : $patente->nro_registro }}
    </p>
    <p>
      <b>1.2 Título: </b>
      {{ $patente->titulo }}
    </p>
    <p>
      <b>1.3 Tipo: </b>
      {{ $patente->tipo }}
    </p>
    <p>
      <b>1.4 Número de expediente: </b>
      {{ $patente->nro_expediente }}
    </p>
    <p>
      <b>1.5 Fecha de presentación: </b>
      {{ $patente->fecha_presentacion }}
    </p>
    <p>
      <b>1.6 Oficina de presentación: </b>
      {{ $patente->oficina_presentacion }}
    </p>
    <p>
      <b>1.7 Certificado: </b>
      @php
        if ($patente->url) {
          echo "Sí";
        } else {
          echo "No";
        }
      @endphp
    </p>

    <h5>II. Manejo de titulares:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 15%;" align="left">Nro</th>
          <th style="width: 85%;" align="left">Titular</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($titulares) == 0)
          <tr>
            <td colspan="2" align="center">
              No hay autores registrados
            </td>
          </tr>
        @endif
        @foreach ($titulares as $titular)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $titular->titular }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <h5>III. Inventor/Autor:</h5>

    <table class="table">
      <thead>
        <tr>
          <th style="width: 5%;" align="left">N°</th>
          <th style="width: 10%;" align="left">Presentador</th>
          <th style="width: 20%;" align="left">Condición</th>
          <th style="width: 40%;" align="left">Nombres</th>
          <th style="width: 25%;" align="left">Tipo</th>
        </tr>
      </thead>
      <tbody>
        @if (sizeof($autores) == 0)
          <tr>
            <td colspan="5" align="center">
              No hay proyectos registrados
            </td>
          </tr>
        @endif
        @foreach ($autores as $autor)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $autor->es_presentador }}</td>
            <td>{{ $autor->condicion }}</td>
            <td>{{ $autor->nombres }}</td>
            <td>{{ $autor->tipo }}</td>
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
