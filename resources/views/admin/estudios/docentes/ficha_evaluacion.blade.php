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
      margin: 130px 20px 20px 20px;
    }

    .head-1 {
      position: fixed;
      top: -100px;
      left: 0px;
      height: 80px;
    }

    .head-1 img {
      margin-left: 120px;
      height: 85px;
    }

    .head-2 {
      position: fixed;
      top: -100px;
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

    .titulo-2 {
      font-size: 15px;
      text-align: center;
    }

    .tableData {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .tableData>tbody td {
      font-size: 11px;
      padding: 5px 3px 6px 3px;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000;
      margin-bottom: 30px;
    }

    .table>thead th {
      font-size: 11px;
      border: 1.5px solid #000;
      font-weight: bold;
    }

    .table>tbody td {
      font-size: 11px;
      border: 1.5px solid #000;
      padding: 5px 3px 6px 3px;
    }

    .row-left {
      text-align: left;
    }

    .row-center {
      text-align: center;
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

  <p class="titulo"><strong>Ficha de Evaluación</strong></p>

  <p class="titulo-2"><strong>{{ $detalles->nombres }}<br>Código docente: {{ $detalles->ser_cod_ant }}</strong></p>

  <div class="cuerpo">

    <table class="table">
      <thead>
        <tr>
          <th style="width: 90%;">Requisitos</th>
          <th style="width: 10%;">Condición</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="row-left">a) Estar en la condición de investigador con calificación vigente en el RENACYT(CONCYTEC)
          </td>
          <td class="row-center">{{ $d1['cumple'] ? 'Sí cumple' : 'No cumple' }}</td>
        </tr>
        <tr>
          <td class="row-left">b) Ser miembro titular de un GI en actividad, reconocido por el VRIP</td>
          <td class="row-center">{{ $d2['cumple'] ? 'Sí cumple' : 'No cumple' }}</td>
        </tr>
        <tr>
          <td class="row-left">c) Haber participado en, al menos, una actividad de investigación cada año durante los
            dos últimos años antes de la presentación de su solicitud</td>
          <td class="row-center">{{ $d3['cumple'] ? 'Sí cumple' : 'No cumple' }}</td>
        </tr>
        <tr>
          <td class="row-left">d) Las publicaciones deben tener afiliación única con la UNMSM, que serán consideradas en
            su calificación</td>
          <td class="row-center">{{ $d4['cumple'] > 0 ? 'Sí cumple' : 'No cumple' }}</td>
        </tr>
        <tr>
          <td class="row-left">e) No presentar deudas de ningún tipo por actividades de investigación</td>
          <td class="row-center">{{ $d5['cumple'] ? 'Sí cumple' : 'No cumple' }}</td>
        </tr>
        <tr>
          <td class="row-left">f) Presentar al VRIP una declaración jurada de no haber incurrido en alguna infracción o
            no tener sanción vigente, contempladas en el Código de Ética de la UNMSM y en el Código Nacional de la
            Integridad Científica del CONCYTEC</td>
          <td class="row-center">{{ $d6['cumple'] ? 'Sí cumple' : 'No cumple' }}</td>
        </tr>
        <tr>
          <td colspan="2"><strong>Directiva para Docentes Investigadores de la Universidad Nacional Mayor de San
              Marcos, Resolución Rectoral N° 009077-2024-R/UNMSM</strong></td>
        </tr>
      </tbody>
    </table>

    <h5>Información:</h5>

    <table class="tableData">
      <tbody>
        <tr>
          <td style="width: 20%;"><strong>Grupo</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">{{ $detalles->grupo_nombre_corto }}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>CTI Vitae</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">{{ $detalles->cti_vitae }}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>Google Scholar</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">{{ $detalles->google_scholar }}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>Orcid</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">{{ $detalles->orcid }}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>Facultad</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">{{ $detalles->facultad }}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>Departamento académico</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">{{ $detalles->des_dep_cesantes }}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>Fecha de emisión</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">{{ date('d/m/Y') }}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>Califica como</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">DOCENTE INVESTIGADOR</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>Evaluación técnica</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">{{ $detalles->estado_tecnico }}</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>Autoridad</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">DEI</td>
        </tr>
        <tr>
          <td style="width: 20%;"><strong>Confirmación</strong></td>
          <td style="width: 1%;">:</td>
          <td style="width: 79%;">NO</td>
        </tr>
      </tbody>
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
