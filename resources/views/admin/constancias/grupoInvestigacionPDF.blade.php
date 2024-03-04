@php
  use Carbon\Carbon;

  $fecha = Carbon::now();
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
      margin: 145px 20px 20px 20px;
    }

    .head-1 {
      position: fixed;
      top: -115px;
      left: 0px;
      height: 90px;
    }

    .head-1 img {
      margin-left: 120px;
      background: red;
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
      font-size: 11px;
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

    .texto {
      font-size: 13px;
      margin: 20px 0;
    }

    .table {
      width: 100%;
      border-collapse: separate;
      margin-bottom: 60px;
    }

    .table>tbody {
      border-bottom: 1.5px solid #000;
    }

    .table>thead {
      font-size: 12px;
      font-weight: bold;
      border-top: 1.5px solid #000;
      border-bottom: 1.5px solid #000;
    }

    .table>tbody td {
      font-size: 12px;
      text-align: center;
      padding-top: 2px;
    }

    .extra-1 {
      font-size: 11px;
      text-align: left;
      width: 100%;
    }

    .extra-2 {
      font-size: 11px;
      text-align: right;
      width: 100%;
    }

    .extra-firma {
      font-size: 11px;
      text-align: center;
      width: 100%;
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
    <p class="user">
      ichajaya
    </p>
  </div>
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <div class="cuerpo">
    <p class="titulo">
      <strong>
        Constancia de Grupos de Investigación de la Universidad Nacional Mayor de San Marcos
      </strong>
    </p>
    <p class="texto">
      El Vicerrector de Investigación y Posgrado de la Universidad Nacional Mayor de
      San Marcos hace constar que:
      <br>
      El Profesor(a): <strong>{{ $grupo[0]->nombre }}</strong>
      <br>
      de la facultad de: <strong>{{ $grupo[0]->facultad }}</strong>
      <br>
      registra participación en el(los) siguiente(s) Grupo(s) de Investigación:
    </p>

    <table class="table">
      <thead>
        <tr>
          <th>Nombre<br>corto GI</th>
          <th>Nombre de Grupo</th>
          <th>Condición</th>
          <th>Resolución<br>Rectoral</th>
          <th>Fecha de<br>Creación GI</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($grupo as $item)
          <tr>
            <td>{{ $item->grupo_nombre_corto }}</td>
            <td>{{ $item->grupo_nombre }}</td>
            <td>{{ $item->cargo }}</td>
            <td>{{ $item->resolucion_rectoral }}</td>
            <td>{{ $item->resolucion_creacion_fecha }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <p class="extra-1"><strong>Se expide la presente constancia a solicitud de interesado(a).</strong></p>
    <br>
    <p class="extra-2"><strong>Lima {{ $fecha->isoFormat('DD') }} de {{ ucfirst($fecha->monthName) }} de
        {{ $fecha->year }}</strong></p>
    <br>
    <p class="extra-firma">
      <strong>
        Dr. José Segundo Niño Montero
        <br>
        Vicerrector
      </strong>
    </p>

  </div>

  <script type="text/php">
    if (isset($pdf)) {
      $x = 530;
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

</html>
