@php
  use Carbon\Carbon;
  $total1 =
      ($publicaciones['37']['cuenta'] ?? 0) * ($publicaciones['37']['puntaje'] ?? 0) +
      ($publicaciones['40']['cuenta'] ?? 0) * ($publicaciones['40']['puntaje'] ?? 0) +
      ($publicaciones['38']['cuenta'] ?? 0) * ($publicaciones['38']['puntaje'] ?? 0) +
      ($publicaciones['41']['cuenta'] ?? 0) * ($publicaciones['41']['puntaje'] ?? 0) +
      ($publicaciones['39']['cuenta'] ?? 0) * ($publicaciones['39']['puntaje'] ?? 0) +
      ($publicaciones['42']['cuenta'] ?? 0) * ($publicaciones['42']['puntaje'] ?? 0) +
      ($publicaciones['43']['cuenta'] ?? 0) * ($publicaciones['43']['puntaje'] ?? 0) +
      ($publicaciones['44']['cuenta'] ?? 0) * ($publicaciones['44']['puntaje'] ?? 0) +
      ($publicaciones['45']['cuenta'] ?? 0) * ($publicaciones['45']['puntaje'] ?? 0) +
      ($publicaciones['52']['cuenta'] ?? 0) * ($publicaciones['52']['puntaje'] ?? 0) +
      ($publicaciones['54']['cuenta'] ?? 0) * ($publicaciones['54']['puntaje'] ?? 0) +
      ($publicaciones['56']['cuenta'] ?? 0) * ($publicaciones['56']['puntaje'] ?? 0) +
      ($publicaciones['57']['cuenta'] ?? 0) * ($publicaciones['57']['puntaje'] ?? 0) +
      ($publicaciones['53']['cuenta'] ?? 0) * ($publicaciones['53']['puntaje'] ?? 0) +
      ($publicaciones['55']['cuenta'] ?? 0) * ($publicaciones['55']['puntaje'] ?? 0) +
      ($patentes['Patente de invención'] ?? 0) * 6 +
      ($patentes['Modelo de utilidad'] ?? 0) * 7 +
      ($patentes['Certificado de obtentor'] ?? 0) * 2;

  $total2 =
      ($publicaciones['63']['cuenta'] ?? 0) * ($publicaciones['63']['puntaje'] ?? 0) +
      ($publicaciones['64']['cuenta'] ?? 0) * ($publicaciones['64']['puntaje'] ?? 0) +
      ($publicaciones['65']['cuenta'] ?? 0) * ($publicaciones['65']['puntaje'] ?? 0) +
      ($publicaciones['66']['cuenta'] ?? 0) * ($publicaciones['66']['puntaje'] ?? 0) +
      ($publicaciones['67']['cuenta'] ?? 0) * ($publicaciones['67']['puntaje'] ?? 0);

  $total3 = $renacyt * 2;
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
      margin: 145px 40px 35px 40px;
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
    }

    .subtitulo {
      font-size: 14px;
      text-align: center;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      border: 1.5px solid #000;
      margin-bottom: 30px;
    }

    .table>thead th {
      font-size: 10px;
      border: 1.5px solid #000;
      padding: 5px 3px 6px 3px;
      font-weight: bold;
    }

    .table>tbody td {
      font-size: 11px;
      border: 1.5px solid #000;
      /* padding: 5px 3px 6px 3px; */
    }

    .th1 {
      width: 60%;
    }

    .th2 {
      width: 25%;
    }

    .th3 {
      width: 13.3%;
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
      Calificación de grupo de investigación
    </strong>
  </p>

  <table class="table">
    <tbody>
      <tr>
        <td class="th1" colspan="2"><strong>Rubro 1. Producción científica/tecnológica</strong></td>
        <td class="th3">Cantidad</td>
        <td class="th3">Puntaje</td>
        <td class="th3">Puntaje Obtenido</td>
      </tr>

      <tr>
        <td class="th2" rowspan="3">1.1. Artículos publicados en revistas indizadas en JCR (Journal Citation
          Reports)</td>
        <td>A) Artículo primario: <strong>6.0 ptos.</strong></td>
        <td>0</td>
        <td>0</td>
        <td rowspan="18">
          {{ $total1 }}
        </td>
      </tr>
      <tr>
        <td>B) Artículo de revisión: <strong>7.0 ptos.</strong></td>
        <td>0</td>
        <td>0</td>
      </tr>
      <tr>
        <td>C) Comunicaciones, notas cortas y reseñas de libros: <strong>2.0 ptos.</strong></td>
        <td>0</td>
        <td>0</td>
      </tr>

      <tr>
        <td class="th2" rowspan="3">1.2 Artículos publicados en revistas indizadas en WoS/ Scopus /Medline</td>
        <td>A) Artículo primario: <strong>6.0 ptos.</strong></td>
        <td>
          {{ ($publicaciones['37']['cuenta'] ?? 0) + ($publicaciones['40']['cuenta'] ?? 0) }}
        </td>
        <td>
          {{ ($publicaciones['37']['cuenta'] ?? 0) * ($publicaciones['37']['puntaje'] ?? 0) + ($publicaciones['40']['cuenta'] ?? 0) * ($publicaciones['40']['puntaje'] ?? 0) }}
        </td>
      </tr>
      <tr>
        <td>B) Artículo de revisión: <strong>7.0 ptos.</strong></td>
        <td>{{ ($publicaciones['38']['cuenta'] ?? 0) + ($publicaciones['41']['cuenta'] ?? 0) }}</td>
        <td>
          {{ ($publicaciones['38']['cuenta'] ?? 0) * ($publicaciones['38']['puntaje'] ?? 0) + ($publicaciones['41']['cuenta'] ?? 0) * ($publicaciones['41']['puntaje'] ?? 0) }}
        </td>
      </tr>
      <tr>
        <td>C) Comunicaciones y notas cortas: <strong>2.0 ptos.</strong></td>
        <td>{{ ($publicaciones['39']['cuenta'] ?? 0) + ($publicaciones['42']['cuenta'] ?? 0) }}</td>
        <td>
          {{ ($publicaciones['39']['cuenta'] ?? 0) * ($publicaciones['39']['puntaje'] ?? 0) + ($publicaciones['42']['cuenta'] ?? 0) * ($publicaciones['42']['puntaje'] ?? 0) }}
        </td>
      </tr>

      <tr>
        <td class="th2" rowspan="3">1.3 Artículos publicados en revistas indizadas en Scielo o reconocidas por el
          Fondo Editorial
          de la UNMSM</td>
        <td>A) Artículo primario: <strong>6.0 ptos.</strong></td>
        <td>{{ $publicaciones['43']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['43']['cuenta'] ?? 0) * ($publicaciones['43']['puntaje'] ?? 0) }}
        </td>
      </tr>
      <tr>
        <td>B) Artículo de revisión: <strong>7.0 ptos.</strong></td>
        <td>{{ $publicaciones['44']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['44']['cuenta'] ?? 0) * ($publicaciones['44']['puntaje'] ?? 0) }}
        </td>
      </tr>
      <tr>
        <td>C) Comunicaciones y notas cortas: <strong>2.0 ptos.</strong></td>
        <td>{{ $publicaciones['45']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['45']['cuenta'] ?? 0) * ($publicaciones['45']['puntaje'] ?? 0) }}
        </td>
      </tr>

      <tr>
        <td class="th2" rowspan="2">1.4 Autor de libros</td>
        <td>A) De editorial internacional: <strong>6.0 ptos.</strong></td>
        <td>{{ $publicaciones['52']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['52']['cuenta'] ?? 0) * ($publicaciones['52']['puntaje'] ?? 0) }}
        </td>
      </tr>
      <tr>
        <td>B) De editorial nacional: <strong>7.0 ptos.</strong></td>
        <td>{{ $publicaciones['54']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['54']['cuenta'] ?? 0) * ($publicaciones['54']['puntaje'] ?? 0) }}
        </td>
      </tr>

      <tr>
        <td class="th2" rowspan="2">1.5 Capítulos de libro</td>
        <td>A) De editorial internacional: <strong>6.0 ptos.</strong></td>
        <td>{{ $publicaciones['56']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['56']['cuenta'] ?? 0) * ($publicaciones['56']['puntaje'] ?? 0) }}
        </td>
      </tr>
      <tr>
        <td>B) De editorial nacional: <strong>7.0 ptos.</strong></td>
        <td>{{ $publicaciones['57']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['57']['cuenta'] ?? 0) * ($publicaciones['57']['puntaje'] ?? 0) }}
        </td>
      </tr>

      <tr>
        <td class="th2" rowspan="2">1.6 Edición de libros</td>
        <td>A) Publicado por una editorial internacional: <strong>6.0 ptos.</strong></td>
        <td>{{ $publicaciones['53']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['53']['cuenta'] ?? 0) * ($publicaciones['53']['puntaje'] ?? 0) }}
        </td>
      </tr>
      <tr>
        <td>B) Publicado por una editorial nacional: <strong>7.0 ptos.</strong></td>
        <td>{{ $publicaciones['55']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['55']['cuenta'] ?? 0) * ($publicaciones['55']['puntaje'] ?? 0) }}
        </td>
      </tr>

      <tr>
        <td class="th2" rowspan="3">1.7 Propiedad intelectual/industrial registrada en Indecopi</td>
        <td>A) Patente de invención: <strong>6.0 ptos.</strong></td>
        <td>{{ $patentes['Patente de invención'] ?? 0 }}</td>
        <td>
          {{ ($patentes['Patente de invención'] ?? 0) * 6 }}
        </td>
      </tr>
      <tr>
        <td>B) Modelo de utilidad: <strong>7.0 ptos.</strong></td>
        <td>{{ $patentes['Modelo de utilidad'] ?? 0 }}</td>
        <td>
          {{ ($patentes['Modelo de utilidad'] ?? 0) * 7 }}
        </td>
      </tr>
      <tr>
        <td>C) Certificado de obtentor: <strong>2.0 ptos.</strong></td>
        <td>{{ $patentes['Certificado de obtentor'] ?? 0 }}</td>
        <td>
          {{ ($patentes['Certificado de obtentor'] ?? 0) * 2 }}
        </td>
      </tr>

      <tr>
        <td class="th1" colspan="2"><strong>Rubro 2. Formación de recursos humanos</strong></td>
        <td class="th3"></td>
        <td class="th3"></td>
        <td class="th3"></td>
      </tr>

      <tr>
        <td class="th2" rowspan="4">2.1. Formación de recursos humanos: Asesoría de tesis o trabajo de
          investigación</td>
        <td>A) Tesis de posgrado doctoral: <strong>3.0 ptos.</strong></td>
        <td>{{ $publicaciones['63']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['63']['cuenta'] ?? 0) * ($publicaciones['63']['puntaje'] ?? 0) }}
        </td>
        <td rowspan="4">
          {{ $total2 }}
        </td>
      </tr>
      <tr>
        <td>B) Tesis de posgrado maestría: <strong>2.0 ptos.</strong></td>
        <td>{{ $publicaciones['64']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['64']['cuenta'] ?? 0) * ($publicaciones['64']['puntaje'] ?? 0) }}
        </td>
      </tr>
      <tr>
        <td>C) Tesis para título profesional o segunda especialidad: <strong>1.5 ptos.</strong></td>
        <td>{{ ($publicaciones['65']['cuenta'] ?? 0) + ($publicaciones['66']['cuenta'] ?? 0) }}</td>
        <td>
          {{ ($publicaciones['65']['cuenta'] ?? 0) * ($publicaciones['65']['puntaje'] ?? 0) + ($publicaciones['66']['cuenta'] ?? 0) * ($publicaciones['66']['puntaje'] ?? 0) }}
        </td>
      </tr>
      <tr>
        <td>D) Trabajo de investigación para bachillerato: <strong>1.0 ptos.</strong></td>
        <td>{{ $publicaciones['67']['cuenta'] ?? 0 }}</td>
        <td>
          {{ ($publicaciones['67']['cuenta'] ?? 0) * ($publicaciones['67']['puntaje'] ?? 0) }}
        </td>
      </tr>

      <tr>
        <td class="th1" colspan="2"><strong>Rubro 3. Reconocimiento de méritos académicos de gestión de los
            investigadores
            titulares</strong></td>
        <td class="th3"></td>
        <td class="th3"></td>
        <td class="th3"></td>
      </tr>

      <tr>
        <td class="th2" rowspan="5">3.1. Dedicación a la investigación. Haber sido responsable o coordinador
          general de proyecto
          con la UNMSM como entidad ejecutora</td>
        <td>A) Con fondo externo internacional superior a 200.000.00 soles: <strong>5.0 ptos.</strong></td>
        <td>0</td>
        <td>0</td>
        <td rowspan="6">{{ $renacyt * 2 }}</td>
      </tr>
      <tr>
        <td>B) Con fondo externo internacional inferior a 200.000.00 soles: <strong>1.5 ptos.</strong></td>
        <td>0</td>
        <td>0</td>
      </tr>
      <tr>
        <td>C) Con fondo externo nacional superior a 200.000.00 soles: <strong>1.5 ptos.</strong></td>
        <td>0</td>
        <td>0</td>
      </tr>
      <tr>
        <td>D) Con fondo externo nacional inferior a 200.000.00 soles: <strong>1.5 ptos.</strong></td>
        <td>0</td>
        <td>0</td>
      </tr>
      <tr>
        <td>E) Responsable de proyecto con fondo UNMSM: <strong>1.5 ptos.</strong></td>
        <td>0</td>
        <td>0</td>
      </tr>

      <tr>
        <td>3.2 Investigador UNMSM-RENACYT</td>
        <td>A) Investigador calificado: <strong>2 ptos.</strong></td>
        <td>{{ $renacyt }}</td>
        <td>
          {{ $total3 }}
        </td>
      </tr>

      <tr>
        <td></td>
        <td></td>
        <td class="th3"></td>
        <td class="th3">Total</td>
        <td class="th3">{{ $total1 + $total2 + $total3 }}</td>
      </tr>
    </tbody>
  </table>

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
