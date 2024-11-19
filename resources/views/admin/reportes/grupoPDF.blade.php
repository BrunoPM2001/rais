@php
  $grupoActual = '';
  $numGrupoActual = 1;
  $firstEl = 0;
  $currentTipo = '';
  $cant_miembros = 0;
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
      height: 85px;
    }

    .head-0 {
      position: fixed;
      top: -135px;
      left: 0;
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

    .head-0 .fecha {
      font-size: 8px;
      margin-top: 10px;
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

    .texto {
      font-size: 13px;
      margin: 20px 0;
    }

    .table_area_fac {
      width: 100%;
      font-style: oblique;
    }

    .table_area_fac>tbody td {
      font-size: 11px;
    }

    .table1 {
      width: 100%;
      border-collapse: separate;
      margin-bottom: 10px;
    }

    .table1>tbody {
      border-bottom: 1.5px solid #000;
    }

    .table1>thead {
      margin-top: -1px;
      font-size: 11px;
      font-weight: bold;
      border-top: 1.5px solid #000;
      border-bottom: 1.5px solid #000;
    }

    .table1>tbody td {
      font-size: 11px;
      text-align: center;
      padding-top: 2px;
    }

    .table2 {
      width: 100%;
      padding-bottom: 5px;
      border-collapse: separate;
      border-bottom: 1.5px solid #000;
    }

    .table2>thead {
      margin-top: -1px;
      font-size: 11px;
      font-style: oblique;
    }

    .table2>tbody td {
      font-size: 10px;
      text-align: left;
      padding-top: 2px;
      padding-left: 2px;
    }

    .cuenta {
      font-size: 11px;
      font-weight: bold;
      margin-top: 5px;
      margin-bottom: 40px;
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

    .nombre_grupo {
      font-style: oblique;
    }
  </style>
</head>


<body>
  <div class="head-1">
    <img src="{{ public_path('head-pdf.jpg') }}" alt="Header">
    <p class="titulo">
      <strong>
        Grupos de Investigación
      </strong>
    </p>
  </div>
  <div class="head-0">
    <p class="fecha">
      Fecha: {{ date('d/m/Y') }}<br>
      Hora: {{ date('H:i:s') }}
    </p>
    <br>
  </div>
  <div class="head-2">
    <p class="rais">© RAIS</p>
    <br>
    <p class="user">
      rortega
    </p>
  </div>
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>

  <div class="cuerpo">
    <table class="table_area_fac">
      <tbody>
        <tr>
          <td style="text-align: left; width: 50%"><strong>Área: </strong>{{ $item->sigla }} {{ $item->area }}</td>
          <td style="text-align: right; width: 50%"><strong>Facultad: </strong>{{ $item->facultad_grupo }}</td>
        </tr>
      </tbody>
    </table>
    @foreach ($lista as $item)
      @if ($grupoActual != $item->grupo_nombre)
        @if ($firstEl == 1)
          </tbody>
          </table>
          <p class="cuenta">Total de integrantes: {{ $cant_miembros }}</p>
        @endif
        @php
          $cant_miembros = 0;
          $firstEl = 1;
        @endphp
        <table class="table1">
          <thead>
            <tr>
              <th style="width: 5%;">Nro.</th>
              <th style="width: 15%;">Nombre corto GI</th>
              <th style="width: 60%;">Nombre de Grupo</th>
              <th style="width: 10%;">Estado</th>
              <th style="width: 20%;">Categoría</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ $numGrupoActual }}</td>
              <td>{{ $item->grupo_nombre_corto }}</td>
              <td class="nombre_grupo">{{ $item->grupo_nombre }}</td>
              <td>
                @switch($item->estado)
                  @case(1)
                    Reconocido
                  @break

                  @case(2)
                    Observado
                  @break

                  @case(4)
                    Registrado
                  @break

                  @case(5)
                    Enviado
                  @break

                  @case(6)
                    En proceso
                  @break
                @endswitch
              </td>
              <td>{{ $item->grupo_categoria }}</td>
            </tr>
          </tbody>
        </table>
        <table class="table2">
          <thead>
            <tr>
              <th>Condición</th>
              <th>Código</th>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Facultad</th>
            </tr>
          </thead>
          <tbody>
            @php
              $numGrupoActual++;
            @endphp
      @endif
      @if ($currentTipo != $item->condicion)
        <tr>
          <td>{{ $item->condicion }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
      @endif
      <tr>
        <td></td>
        <td>{{ $item->codigo }}</td>
        <td style="text-transform: uppercase">{{ $item->nombre }}</td>
        <td>{{ $item->tipo }}</td>
        <td>{{ $item->facultad_miembro }}</td>
      </tr>
      @php
        $cant_miembros++;
        $currentTipo = $item->condicion;
        $grupoActual = $item->grupo_nombre;
      @endphp

      @if ($loop->last)
          </tbody>
        </table>
        <p class="cuenta">Total de integrantes: {{ $cant_miembros }}</p>
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
