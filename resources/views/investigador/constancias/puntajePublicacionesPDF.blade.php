@php
  use Carbon\Carbon;

  $fecha = Carbon::now();
  $currentTipo = '';
  $subtotal = 0;
  $cantidad = 0;
  $puntajetotal = 0.0;

  foreach ($publicaciones as $item) {
      $puntajetotal += $item->puntaje;
      $cantidad += $item->cantidad;
  }

  foreach ($patentes as $patente) {
      $puntajetotal += $patente->puntaje;
      $cantidad += $patente->cantidad;
  }

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
      font-family: Arial, sans-serif;
    }

    /* @page {
        margin: 145px 20px 20px 20px;
        } */

    .header-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 20px;
    }

    .header-table td {
      vertical-align: middle;
      /* Centra el contenido verticalmente */
    }

    .header-left {
      width: 14%;
      /* Espacio fijo para la izquierda */
      text-align: left;
      /* Alineación a la izquierda */
      font-size: 10px;
    }

    .header-center {
      width: 72%;
      /* Espacio amplio para la imagen */
      text-align: center;
      /* Centra el contenido */
    }

    .header-right {
      width: 14%;
      /* Espacio fijo para la derecha */
      text-align: right;
      /* Alineación a la derecha */
      font-size: 10px;
    }

    .header-center img {
      max-width: 100%;
      max-height: 100px;
      /* Controla la altura de la imagen */
      object-fit: contain;
      /* Evita la deformación */
    }

    .cuerpo-table {
      width: 100%;
      text-align: center;
      border-collapse: collapse;
      table-layout: fixed;
      margin-bottom: 5px;
    }

    .title {
      font-size: 20px;
      text-align: center;
      margin-top: 20px;
      margin-bottom: 20px;
      color: #0a0a84;
    }

    .div {
      position: fixed;
      top: -15px;
      width: 100%;
      height: 0.5px;
      background: #000;
    }



    .texto,
    .texto-1 {
      font-size: 12px;
      margin: 20px 0;
    }


    .subhead {
      width: 100%;
      margin-top: 0;
      margin-bottom: 0;
      font-size: 14px;
      text-align: center;
    }

    .table {
      width: 100%;
      border-collapse: separate;
      margin-bottom: 40px;
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
      padding-top: 2px;
    }

    .table>tfoot td {
      font-size: 12px;
      padding-top: 2px;
    }

    .row-left {
      text-align: left;
    }

    .row-center {
      text-align: center;
    }

    .row-right {
      text-align: right;
      padding-right: 10px;
    }


    .extra-1,
    .extra-2,
    {
    font-size: 12px;

    }

    .extra-firma {
      font-size: 14px;
    }

    .table-footer {
      width: 100%;
      text-align: center;
      margin-top: 70px;

    }

    .foot-1 {
      width: 100%;
      position: fixed;
      bottom: -20px;
      left: 0px;
      text-align: left;
      font-size: 10px;
    }

    .firma {
      width: 150px;
    }

    .sello {
      width: 100px;
    }

    .table-sello-firma {
      margin: auto;
      width: 60%;
      text-align: center;
      margin-top: 50px;
    }

    .nombre-vice {
      top: -28px;
      position: relative;
      text-align: center;
      font-size: 14px;
    }
  </style>
</head>

<body>
  <table class="header-table">
    <tr>
      <td class="header-left">
        <span>Fecha: {{ date('d/m/Y') }}</span><br>
        <span>Hora: {{ date('H:i:s') }}</span>
      </td>
      <td class="header-center">
        <img src="{{ public_path('head-pdf.jpg') }}" alt="Header">
      </td>
      <td class="header-right">
        <span>© RAIS</span><br>
        @if ($username)
          <span>Usuario: {{ $username }}</span>
        @endif
      </td>
    </tr>
  </table>
  <table class="cuerpo-table">
    <tr class="title">
      <td><b>Constancia de Puntaje de Publicaciones</b></td>
    </tr>
  </table>

  <table class="texto-1">
    <tr>
      <td> El Vicerrector de Investigación y Posgrado de la Universidad Nacional Mayor de
        San Marcos hace constar que:</td>
    </tr>
  </table>

  <table style="width: 100%;  font-size: 12px; margin-top: 15px;">
    <tr>
      <!-- Primera columna (50%) -->
      <td style="width: 65%; vertical-align: top;border-collapse:collapse; padding:10px;border: 1px solid #111111;">
        <table style="width: 100%; height: 70px; height: 70px;">
          <tr>
            <td>Apellidos:</td>
            <td><strong>{{ $docente->apellido1 . ' ' . $docente->apellido2 }}</strong></td>
          </tr>
          <tr>
            <td>Nombres:</td>
            <td><strong>{{ $docente->nombres }}</strong></td>
          </tr>
          <tr>
            <td>Facultad:</td>
            <td><strong>{{ $docente->facultad }}</strong></td>
          </tr>
          <tr>
            <td>DNI:</td>
            <td><strong>{{ $docente->doc_numero }}</strong></td>
          </tr>
          <tr>
            <td>Categoria :</td>
            <td> <strong>{{ $docente->categoria }}</strong></td>
          </tr>
          <tr>
            <td>Clase :</td>
            <td> <strong>{{ $docente->clase }}</strong></td>
          </tr>
        </table>
      </td>

      <!-- Segunda columna (50%) -->
      <td style="width: 35%; vertical-align: middle; border-collapse: collapse; border: 1px solid #111111">
        <table style="width: 100%; padding: 10px; height: 70px; border-collapse: collapse;">
          <tr>
            <td>Numero de publicaciones:</td>
            <td><strong>{{ $cantidad }}</strong></td>
          </tr>
          <tr>
            <td>Puntaje total:</td>
            <td><strong>{{ number_format($puntajetotal, 2) }}</strong></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <table class="texto-1">
    <tr>
      <td>Ha registrado las siguiente publicaciones:</td>
    </tr>
  </table>

  <div class="cuerpo">

    {{-- <p class="subhead"><strong>Puntaje de publicaciones registradas a partir del 1ro de abril de 2008</strong></p> --}}

    <table class="table">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Categoria</th>
          <th>Número</th>
          <th>Puntaje</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($publicaciones as $item)
          @if ($currentTipo != $item->titulo)
            <tr>
              <td class="row-left">{{ $item->titulo }}</td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          @endif
          <tr>
            <td></td>
            <td class="row-left">{{ $item->categoria }}</td>
            <td class="row-center">{{ $item->cantidad }}</td>
            <td class="row-right">{{ $item->puntaje }}</td>
          </tr>
          @php
            $currentTipo = $item->titulo;
            $subtotal += $item->cantidad;
          @endphp
        @endforeach
        @if (count($patentes) > 0)
          <tr>
            <td class="row-left">Patente</td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          @foreach ($patentes as $patente)
            <tr>
              <td></td>
              <td class="row-left">{{ $patente->tipo }}</td>
              <td class="row-center">{{ $patente->cantidad }}</td>
              <td class="row-right">{{ $patente->puntaje }}</td>
            </tr>
            @php
              $subtotal += $patente->cantidad;
            @endphp
          @endforeach
        @endif
      </tbody>
      <tfoot>
        <tr>
          <td></td>
          <td class="row-right">Sub-Total</td>
          <td class="row-center">{{ $subtotal }}</td>
          <td class="row-right">{{ number_format($puntajetotal, 2) }}</td>
        </tr>
      </tfoot>
    </table>

  </div>

  <div style="page-break-inside: avoid;">
    <table>
      <tr>
        <td class="extra-1">Se expide la presente constancia a solicitud del(de la) interesado(a) para los fines que
          considere conveniente.</td>
      </tr><br>
      <tr>
        <td class="extra-2">Lima, {{ $fecha->isoFormat('DD') }} de {{ ucfirst($fecha->monthName) }}
          de {{ $fecha->year }}</td>
      </tr>
    </table>

    <table class="table-sello-firma">
      <tr class="extra-firma">
        <td style="width: 100px;">
        </td>
        <td>
          <img class="firma" src="{{ public_path('firma-negra.jpg') }}" alt="Firma">
        </td>
        <td style="margin-top: 15px;">
          <img class="sello" src="{{ public_path('sello.jpg') }}" alt="Firma">
        </td>
      </tr>
    </table>

    <p class="nombre-vice">
      Dr. José Segundo Niño Montero <br><strong>Vicerrector</strong>
    </p>
  </div>

  <div class="foot-1">
    <table style="width: 80%; font-size: 10px;">
      <tr>
        <td style="width: 90%; vertical-align: top; padding-right: 10px;">
          <hr>
          Para verificar la validez de este documento puede ingresar al siguiente enlace:
          <br>
          https://rais.unmsm.edu.pe/minio/constancias/{{ $file }}
          <p style="padding-top: 5; margin: 0;">Registro de Actividades de Investigación de San Marcos - © RAIS</p>
        </td>
        <td style="width: 10%; text-align: right; vertical-align: top;">
          <img src="data:image/png;base64,{{ $qrCode }}" alt="Código QR" style="width: 50px; height: 50px;">
        </td>
      </tr>
    </table>
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
