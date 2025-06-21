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
      margin: 145px 25px 35px 25px;
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

    .foot-1 {
      position: fixed;
      bottom: -15px;
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
      color: #1E4D78
    }

    .tableData {
      width: 100%;
      border: 1px solid #000;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    .tableData>tbody td {
      font-size: 10.5px;
      border: 1px solid #000;
      padding: 7px;
    }

    .cuerpo {
      font-size: 10.5px;
      text-align: justify;
    }

    li {
      margin-bottom: 15px;
    }

    .table-footer {
      width: 100%;
      text-align: center;
      margin-top: 80px;
    }

    .extra-firma {
      font-size: 10.5px;
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
  <div class="div"></div>
  <div class="foot-1">RAIS - Registro de Actividades de Investigación de San Marcos</div>
  <p class="titulo">
    <strong>
      DECLARACIÓN JURADA DE CUMPLIMIENTO PARA RECIBIR ASIGNACIÓN FINANCIERA AL
      PROYECTO DE INVESTIGACIÓN PARA GRUPO DE INVESTIGACIÓN DE LA UNMSM 2024
    </strong>
  </p>

  <div class="cuerpo">

    <table class="tableData">
      <tbody>
        <tr>
          <td colspan="2">Conste por el presente documento yo:
            <strong> {{ $detalle->responsable }}</strong>
          </td>
        </tr>
        <tr>
          <td colspan="2">Profesor nombrado de la Facultad de:
            <strong> {{ $detalle->facultad }}</strong>
          </td>
        </tr>
        <tr>
          <td>Código Docente N°:
            <strong> {{ $detalle->codigo }}</strong>
          </td>
          <td>Categoría:
            <strong> {{ $detalle->categoria }}</strong>
          </td>
        </tr>
        <tr>
          <td>DNI N°:
            <strong> {{ $detalle->doc_numero }}</strong>
          </td>
          <td>Clase:
            <strong> {{ $detalle->clase }}</strong>
          </td>
        </tr>
        <tr>
          <td colspan="2">Grupo de investigación:
            <strong> {{ $detalle->grupo_nombre }}</strong>
          </td>
        </tr>
        <tr>
          <td colspan="2">En mi calidad de responsable del proyecto de investigación titulado:<br>
            <strong> {{ $detalle->titulo }}</strong>
          </td>
        </tr>
        <tr>
          <td>Código de proyecto:
            <strong> {{ $detalle->codigo_proyecto }}</strong>
          </td>
          <td>Monto asignado S/:
            <strong>{{ $detalle->presupuesto }}</strong>
          </td>
        </tr>
      </tbody>
    </table>

    <p>Declaro bajo juramento conocer las directivas,los procedimientos y el cronograma de ejecución de los Proyectos de
      Investigación 2024 y me comprometo a cumplirlos; en particular, declaro conocer los siguientes puntos:
    </p>

    <ol>
      <li>
        El Vicerrectorado de Investigación y Posgrado (VRIP) otorga la Subvención Financiera 2024, de acuerdo con lo
        estipulado en la “Política de Financiamiento de la Investigación de la Universidad Nacional Mayor de San Marcos”
        (R.R. N.00896-R-17 y modificatorias).
      </li>
      <li>
        Los informes económicos se presentarán de acuerdo con la “Directiva para la rendición económica de los fondos
        otorgados por la UNMSM para los proyectos de Programas de Investigación del Vicerrectorado de Investigación y
        Posgrado, año 2024.
      </li>
      <li>
        El informe económico final, con documentos físicos sustentatorios en original, deberá presentarse en el plazo
        establecido en la directiva antes mencionada.
      </li>
      <li>
        Los recursos económicos entregados serán utilizados exclusivamente para los fines señalados en el plan de
        actividades y en el presupuesto aprobado por el VRIP a través del sistema RAIS. Su incumplimiento conlleva a la
        devolución del importe asignado a la universidad.
      </li>
      <li>
        En el caso de no rendir cuenta documentada en las fechas señaladas, o habiendo rendido, la Oficina de Control
        Previo y Fiscalización observe alguno, algunos o todos los documentos de gastos y los cuales no pueden ser
        subsanados, me comprometo a devolver el dinero recibido, más los intereses legales de corresponder y me someto a
        otras acciones que correspondan según establezca la Oficina de Asesoría Legal de la Universidad Nacional Mayor
        de San Marcos.
      </li>
      <li>
        Los equipos, instrumentos y libros que se adquieran (siguiendo lo establecido en la R.D. N° 00366-DGA-2019:
        “Instructivo de manejo, control y ejecución de fondos asignados a proyectos de investigación para la adquisición
        de bienes de capital de la Universidad Nacional Mayor de San Marcos” y sus modificatorias) deberán entregarse a
        la Facultad o dependencia correspondiente. Culminado el proyecto, los bienes deben permanecer en las
        instalaciones de la facultad (Instituto de Investigación, Unidad de Investigación, Grupos de Investigación, o
        laboratorios).
      </li>
      <li>
        Si devuelvo dinero a la UNMSM por más del 10% del monto asignado, no podré participar en las actividades de
        investigación para el año 2025.
      </li>
    </ol>

    <p>
      En caso de incumplimiento de la presente declaración, el VRIP se reserva el derecho de cancelar el apoyo al
      proyecto, así como las subvenciones a los investigadores sin derecho a reintegro, de acuerdo con las normas
      vigentes.
    </p>

    <p>
      Por tanto, en mi calidad de responsable del citado proyecto de investigación, acepto haber leído y cumplir con las
      condiciones establecidas en la presente declaración para recibir asignación financiera al proyecto, así como
      asistir al taller de capacitación 2024. Habiendo aceptado las condiciones, doy conformidad y envío la declaración
      debidamente firmada vía sistema RAIS.
    </p>

    <div style="page-break-inside: avoid;">
      <table class="table-footer">
        <tr class="extra-firma">
          <td><strong>{{ $detalle->responsable }}</strong><br>DNI N°. <strong>{{ $detalle->doc_numero }}</strong></td>
        </tr>
      </table>
    </div>

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
