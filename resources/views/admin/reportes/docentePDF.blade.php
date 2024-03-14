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
      margin: 165px 20px 20px 20px;
    }

    .head-1 {
      position: fixed;
      top: -135px;
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
      top: -135px;
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
      top: -45px;
      width: 100%;
      height: 0.5px;
      background: #000;
    }

    .titulo {
      width: 754px;
      font-size: 16px;
      text-align: center;
    }

    .table1 {
      width: 100%;
      border-collapse: separate;
      margin-bottom: 30px;
    }

    .table1>tbody {
      border-bottom: 1.5px solid #000;
    }

    .table1>thead {
      margin-top: -1px;
      font-size: 10px;
      border-top: 1.5px solid #000;
      border-bottom: 1.5px solid #000;
    }

    .table1>thead th {
      font-weight: normal;
    }

    .table1>tbody td {
      font-size: 10px;
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

    .top {
      margin-left: 2px;
    }

    .top_left {
      display: inline;
      width: 40%;
      font-size: 10px;
      text-align: right;
    }

    .top_right {
      display: inline;
      width: 40%;
      font-size: 10px;
      text-align: right;
      margin-top: 5px;
      margin-right: 2px;
      float: right;
    }
  </style>
</head>


<body>
  <div class="head-1">
    <img src="{{ public_path('head-pdf.jpg') }}" alt="Header">
    <p class="titulo">
      <strong>
        Reporte de proyectos por docente
      </strong>
    </p>
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
    <!--  Proyectos antiguos  -->
    @foreach ($proyectos_antiguos as $item)
      @if ($loop->first)
        <div class="top">
          <div class="top_left"><strong>Proyectos anteriores al 2017</strong></div>
          <div class="top_right"><strong>Nombres:</strong> {{ $investigador[0]->nombres }}</div>
        </div>
        <table class="table1">
          <thead>
            <tr>
              <th>Nro.</th>
              <th>Código</th>
              <th>Título del proyecto</th>
              <th>Periodo</th>
              <th>Tipo</th>
              <th>Condición</th>
            </tr>
          </thead>
          <tbody>
      @endif
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $item->codigo }}</td>
        <td>{{ $item->titulo }}</td>
        <td>{{ $item->periodo }}</td>
        <td>{{ $item->tipo }}</td>
        <td>{{ $item->condicion }}</td>
      </tr>
      @if ($loop->last)
        </tbody>
        </table>
      @endif
    @endforeach
    <!--  Proyectos nuevos  -->
    @foreach ($proyectos_nuevos as $item)
      @if ($loop->first)
        <div class="top">
          <div class="top_left"><strong>Proyectos posteriores al 2017</strong></div>
          <div class="top_right"><strong>Nombres:</strong> {{ $investigador[0]->nombres }}</div>
        </div>
        <table class="table1">
          <thead>
            <tr>
              <th>Nro.</th>
              <th>Código</th>
              <th>Título del proyecto</th>
              <th>Periodo</th>
              <th>Tipo</th>
              <th>Condición</th>
            </tr>
          </thead>
          <tbody>
      @endif
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $item->codigo_proyecto }}</td>
        <td>{{ $item->titulo }}</td>
        <td>{{ $item->periodo }}</td>
        <td>{{ $item->tipo_proyecto }}</td>
        <td>{{ $item->condicion }}</td>
      </tr>
      @if ($loop->last)
        </tbody>
        </table>
      @endif
    @endforeach
  </div>

  <script type="text/php">
    if (isset($pdf)) {
      $x = 527;
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
