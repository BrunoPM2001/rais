<?php

namespace App\Http\Controllers\Investigador\Constancias;

use App\Http\Controllers\S3Controller;
use App\Mail\Investigador\Constancias\ConstanciaFirmada;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReporteController extends S3Controller {

  public function getDatosDocente(Request $request) {
    $docente = DB::table('Usuario_investigador AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id') // LEFT JOIN con Facultad
      ->leftJoin('Repo_rrhh AS rrhh', 'rrhh.ser_doc_id_act', '=', 'a.doc_numero') // LEFT JOIN con Repo_rrhh
      ->leftJoin('Docente_categoria AS c', 'rrhh.ser_cat_act', '=', 'c.categoria_id') // LEFT JOIN con Docente_categoria
      ->select(
        'a.id',
        'a.codigo',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
        'a.doc_numero',
        'a.sexo',
        'a.email3',
        'a.estado',
        'b.nombre AS facultad',
        'c.categoria',
        'c.clase'
      )
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    return $docente;
  }

  public function getConstanciaTesisAsesoria(Request $request, $solicitud = false) {
    $docente = $this->getDatosDocente($request);

    $tesis = DB::table('view_proyecto_reporte AS a')
      ->select(
        '*',

      )
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_proyecto', ['PTPBACHILLER', 'PTPMAEST', 'PTPDOCTO', 'PTPGRADO', 'Tesis'])
      ->orderBy('a.periodo', 'DESC')
      ->get();

    if (!$solicitud) {
      $pdf = Pdf::loadView('admin.constancias.tesisAsesoriaPDF', [
        'docente' => $docente,
        'tesis' => $tesis,
        'username' => false
      ]);

      return $pdf->stream();
    } else {
      $date = Carbon::now();
      $nameFile = 'tesisAsesoria_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '.pdf';
      $nameFileN = 'tesisAsesoria_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '_original.pdf';

      $qrUrl =  env('URL_CONSTANCIAS') . "constancias/" . $nameFile;
      $qrCode = base64_encode(QrCode::format('png')->size(300)->generate($qrUrl));

      $pdf = Pdf::loadView('investigador.constancias.tesisAsesoriaPDF', [
        'docente' => $docente,
        'tesis' => $tesis,
        'username' => false,
        'file' => $nameFile,
        'qrCode' => $qrCode
      ]);

      return ['file' => $pdf->output(), 'name' => $nameFile, 'original_file' => $nameFileN];
    }
  }

  public function getConstanciaEstudiosInvestigacion(Request $request, $solicitud = false) {

    // Inicializar arrays independientes para cada grupo
    $con_incentivo = [];
    $financiamiento_gi = [];
    $no_monetarios_gi = [];
    $sin_incentivo = [];
    $eventos = [];
    $proyecto_publicacion = [];
    $taller = [];
    $pfex = [];
    $sin_con = [];
    $pmulti = [];
    $otros = [];

    $docente = $this->getDatosDocente($request);

    $proyectos = DB::table('view_proyecto_reporte as vreport')
      ->select('*')
      ->where('vreport.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->orderBy('periodo', 'DESC')
      ->get();

    foreach ($proyectos as $proyecto) {
      switch ($proyecto->tipo_proyecto) {
        case 'CON-CON':
          $con_incentivo[] = $proyecto;
          break;
        case 'PCONFIGI':
        case 'PRO-CTIE':
        case 'PINTERDIS':
        case 'PCONFIGI-INV':
          $financiamiento_gi[] = $proyecto;
          break;
        case 'PSINFINV':
        case 'PSINFIPU':
          $no_monetarios_gi[] = $proyecto;
          break;
        case 'SIN-SIN':
          $sin_incentivo[] = $proyecto;
          break;
        case 'PEVENTO':
          $eventos[] = $proyecto;
          break;
        case 'Publicacion':
          $proyecto_publicacion[] = $proyecto;
          break;
        case 'Taller':
          $taller[] = $proyecto;
          break;
        case 'PFEX':
          $pfex[] = $proyecto;
          break;
        case 'SIN-CON':
          $sin_con[] = $proyecto;
          break;
        case 'MULTI':
        case 'PMULTI':
          $pmulti[] = $proyecto;
          break;
        default:
          $otros[] = $proyecto;
          break;
      }
    }

    $fondos_concursables = count($con_incentivo) + count($financiamiento_gi) + count($no_monetarios_gi) + count($sin_incentivo) + count($eventos);
    $otras_actividades = count($proyecto_publicacion) + count($taller);
    $externos = count($pfex) + count($sin_con);


    if (!$solicitud) {

      $pdf = Pdf::loadView('admin.constancias.estudiosInvestigacionPDF', [
        'docente' => $docente, // Pasar el docente
        'con_incentivo' => $con_incentivo, // Pasar cada array por separado
        'financiamiento_gi' => $financiamiento_gi,
        'pmulti' => $pmulti,
        'no_monetarios_gi' => $no_monetarios_gi,
        'sin_incentivo' => $sin_incentivo,
        'eventos' => $eventos,
        'publicaciones' => $proyecto_publicacion,
        'talleres' => $taller,
        'fondos_externos' => $pfex,
        'sin_asignacion_con_incentivo' => $sin_con,
        'fondos_concursables' => $fondos_concursables,
        'otras_actividades' => $otras_actividades,
        'externos' => $externos,
        'otros' => $otros,
        'username' => false
      ]);
      return $pdf->stream();
    } else {
      $date = Carbon::now();
      $nameFile = 'estudios_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '.pdf';
      $nameFileN = 'estudios_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '_original.pdf';

      $qrUrl =  env('URL_CONSTANCIAS') . "constancias/" . $nameFile;
      $qrCode = base64_encode(QrCode::format('png')->size(300)->generate($qrUrl));

      $pdf = Pdf::loadView('investigador.constancias.estudiosInvestigacionPDF', [
        'docente' => $docente, // Pasar el docente
        'con_incentivo' => $con_incentivo, // Pasar cada array por separado
        'financiamiento_gi' => $financiamiento_gi,
        'pmulti' => $pmulti,
        'no_monetarios_gi' => $no_monetarios_gi,
        'sin_incentivo' => $sin_incentivo,
        'eventos' => $eventos,
        'publicaciones' => $proyecto_publicacion,
        'talleres' => $taller,
        'fondos_externos' => $pfex,
        'sin_asignacion_con_incentivo' => $sin_con,
        'fondos_concursables' => $fondos_concursables,
        'otras_actividades' => $otras_actividades,
        'externos' => $externos,
        'otros' => $otros,
        'username' => false,
        'file' => $nameFile,
        'qrCode' => $qrCode
      ]);

      return ['file' => $pdf->output(), 'name' => $nameFile, 'original_file' => $nameFileN];
    }
  }

  public function getConstanciaEquipamientoCientifico(Request $request, $solicitud = false) {
    $docente = $this->getDatosDocente($request);

    $equipamiento = DB::table('view_proyecto_reporte AS a')
      ->join('view_proyecto_presupuesto AS p', 'p.proyecto_id', '=', 'a.proyecto_id')
      ->select(
        'a.periodo',
        'a.codigo_proyecto',
        'a.titulo',
        'a.tipo_proyecto',
        'a.grupo',
        'a.grupo_nombre_corto',
        'a.grupo_categoria',
        'p.total_monto AS presupuesto',
        'a.condicion_gi',
      )
      ->where('a.tipo_proyecto', '=', 'ECI')
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->orderBy('a.periodo', 'DESC')
      ->get();

    if (!$solicitud) {
      $pdf = Pdf::loadView('admin.constancias.equipamientoPDF', [
        'docente' => $docente,
        'equipamiento' => $equipamiento,
        'username' => false
      ]);
      return $pdf->stream();
    } else {
      $date = Carbon::now();
      $nameFile = 'eci_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '.pdf';
      $nameFileN = 'eci_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '_original.pdf';

      $qrUrl =  env('URL_CONSTANCIAS') . "constancias/" . $nameFile;
      $qrCode = base64_encode(QrCode::format('png')->size(300)->generate($qrUrl));

      $pdf = Pdf::loadView('investigador.constancias.equipamientoPDF', [
        'docente' => $docente,
        'equipamiento' => $equipamiento,
        'username' => false,
        'file' => $nameFile,
        'qrCode' => $qrCode
      ]);

      return ['file' => $pdf->output(), 'name' => $nameFile, 'original_file' => $nameFileN];
    }
  }

  public function getConstanciaNoDeuda(Request $request, $solicitud = false) {
    $docente = $this->getDatosDocente($request);

    $deudores = DB::table('view_deudores')
      ->select(
        '*',
      )
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->get();

    $deuda = count($deudores);

    if (!$solicitud) {
      $pdf = Pdf::loadView('admin.constancias.noDeudaPDF', [
        'docente' => $docente,
        'deuda' => $deuda,
        'username' => false
      ]);
      return $pdf->stream();
    } else {
      $date = Carbon::now();
      $nameFile = 'nodeuda_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '.pdf';
      $nameFileN = 'nodeuda_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '_original.pdf';

      $qrUrl =  env('URL_CONSTANCIAS') . "constancias/" . $nameFile;
      $qrCode = base64_encode(QrCode::format('png')->size(300)->generate($qrUrl));

      $pdf = Pdf::loadView('investigador.constancias.noDeudaPDF', [
        'docente' => $docente,
        'deuda' => $deuda,
        'username' => false,
        'file' => $nameFile,
        'qrCode' => $qrCode
      ]);

      return ['file' => $pdf->output(), 'name' => $nameFile, 'original_file' => $nameFileN];
    }
  }

  //  TODO - Ver por qué la suma de puntos no coincide
  public function getConstanciaPuntajePublicaciones(Request $request, $solicitud = false) {

    $docente = $this->getDatosDocente($request);

    $publicaciones = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->join('Publicacion_categoria AS c', 'c.id', '=', 'b.categoria_id')
      ->select(
        'c.titulo',
        'c.categoria',
        DB::raw('COUNT(*) AS cantidad'),
        DB::raw('(a.puntaje * COUNT(*)) AS puntaje')
      )
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.validado', '=', 1)
      ->groupBy('b.categoria_id')
      ->groupBy('c.titulo')
      ->groupBy('c.categoria')
      ->orderBy('c.titulo')
      ->orderBy('c.categoria')
      ->get()
      ->toArray();

    $patentes = DB::table('Patente AS a')
      ->leftJoin('Patente_autor AS b', 'b.patente_id', '=', 'a.id')
      ->leftJoin('Patente_entidad AS c', 'c.patente_id', '=', 'a.id')
      ->select(
        'a.tipo',
        DB::raw('COUNT(*) AS cantidad'),
        DB::raw('SUM(b.puntaje) AS puntaje') // Sumar los puntajes agrupados
      )
      ->where('b.es_presentador', 1)
      ->where('b.investigador_id', $request->attributes->get('token_decoded')->investigador_id)
      ->groupBy('a.tipo') // Agrupación solo por tipo
      ->orderBy('a.tipo') // Ordenar por tipo
      ->get()
      ->toArray();

    if (!$solicitud) {
      $pdf = Pdf::loadView('admin.constancias.puntajePublicacionesPDF', [
        'docente' => $docente,
        'publicaciones' => $publicaciones,
        'patentes' => $patentes,
        'username' => false
      ]);
      return $pdf->stream();
    } else {
      $date = Carbon::now();
      $nameFile = 'puntaje_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '.pdf';
      $nameFileN = 'puntaje_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '_original.pdf';

      $qrUrl =  env('URL_CONSTANCIAS') . "constancias/" . $nameFile;
      $qrCode = base64_encode(QrCode::format('png')->size(300)->generate($qrUrl));

      $pdf = Pdf::loadView('investigador.constancias.puntajePublicacionesPDF', [
        'docente' => $docente,
        'publicaciones' => $publicaciones,
        'patentes' => $patentes,
        'username' => false,
        'file' => $nameFile,
        'qrCode' => $qrCode
      ]);

      return ['file' => $pdf->output(), 'name' => $nameFile, 'original_file' => $nameFileN];
    }
  }

  public function getConstanciaCapituloLibro(Request $request, $solicitud = false) {

    $docente = $this->getDatosDocente($request);

    $publicaciones = DB::table('Publicacion as a')
      ->selectRaw("
        CONCAT(c.apellido1, ' ', c.apellido2, ' ', c.nombres) as investigador,
        d.nombre as nombre,
        YEAR(a.fecha_publicacion) as periodo,
        a.titulo as titulo,
        a.isbn as isbn,
        e.tipo as tipo,
        e.categoria as categoria,
        f.codigo_proyecto as codigo_proyecto,
        IFNULL(
            (
                SELECT MAX(t1.titulo) 
                FROM Proyecto t1 
                WHERE t1.codigo_proyecto = f.codigo_proyecto
            ),
            (
                SELECT MAX(t2.titulo) 
                FROM Proyecto_H t2 
                WHERE t2.codigo = f.codigo_proyecto
            )
        ) as titulo_proyecto,
        f.entidad_financiadora as entidad_financiadora
    ")
      ->join('Publicacion_autor as b', 'a.id', '=', 'b.publicacion_id')
      ->join('Usuario_investigador as c', 'b.investigador_id', '=', 'c.id')
      ->join('Facultad as d', 'c.facultad_id', '=', 'd.id')
      ->join('Publicacion_proyecto as f', 'f.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_categoria as e', 'a.categoria_id', '=', 'e.id')
      ->where('a.estado', 1)
      ->where('b.investigador_id', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_publicacion', ['libro', 'capitulo'])
      ->orderByRaw('6 DESC, 3 DESC, 4') // Ordena por las posiciones en SELECT
      ->get();

    if (!$solicitud) {
      $pdf = Pdf::loadView('admin.constancias.capituloLibroPDF', [
        'docente' => $docente,
        'publicaciones' => $publicaciones,
        'username' => false
      ]);
      return $pdf->stream();
    } else {
      $date = Carbon::now();
      $nameFile = 'capitulos_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '.pdf';
      $nameFileN = 'capitulos_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '_original.pdf';

      $qrUrl =  env('URL_CONSTANCIAS') . "constancias/" . $nameFile;
      $qrCode = base64_encode(QrCode::format('png')->size(300)->generate($qrUrl));

      $pdf = Pdf::loadView('investigador.constancias.capituloLibroPDF', [
        'docente' => $docente,
        'publicaciones' => $publicaciones,
        'username' => false,
        'file' => $nameFile,
        'qrCode' => $qrCode
      ]);

      return ['file' => $pdf->output(), 'name' => $nameFile, 'original_file' => $nameFileN];
    }
  }

  public function getConstanciaPublicacionesCientificas(Request $request, $solicitud = false) {
    $docente = $this->getDatosDocente($request);

    $publicaciones = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->join('Publicacion_categoria AS c', 'c.id', '=', 'b.categoria_id')
      ->join('Usuario_investigador AS d', 'd.id', '=', 'a.investigador_id')
      ->join('Facultad AS e', 'e.id', '=', 'd.facultad_id')
      ->select(
        'c.tipo',
        'c.categoria',
        'a.puntaje',
        'b.lugar_publicacion',
        DB::raw('YEAR(b.fecha_publicacion) AS año'),
        'b.step',
        'b.titulo',
        'b.publicacion_nombre',
        'b.issn',
        'b.isbn',
        'b.universidad',
        'b.pais',
        'b.observaciones_usuario',
        'e.nombre AS facultad'
      )
      ->where('a.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('b.validado', '=', 1)
      ->orderBy('c.tipo')
      ->orderBy('c.categoria')
      ->orderByDesc('año')
      ->orderBy('b.titulo')
      ->get();

    $patentes = DB::table('Patente AS a')
      ->leftJoin('Patente_autor AS b', 'b.patente_id', '=', 'a.id')
      ->leftJoin('Patente_entidad AS c', 'c.patente_id', '=', 'a.id')
      ->select(
        'a.titulo',
        'a.tipo',
        'c.titular',
        'b.puntaje',
        'a.oficina_presentacion',
        DB::raw('YEAR(c.updated_at) año'), // Captura solo el año
      )
      ->where('b.es_presentador', '=', 1)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->orderBy('a.tipo')
      ->orderByDesc('c.updated_at')
      ->orderBy('a.titulo')
      ->get();

    if (!$solicitud) {
      $pdf = Pdf::loadView('admin.constancias.publicacionesCientificasPDF', [
        'docente' => $docente,
        'publicaciones' => $publicaciones,
        'patentes' => $patentes,
        'username' => false
      ]);

      return $pdf->stream();
    } else {
      $date = Carbon::now();
      $nameFile = 'publicaciones_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '.pdf';
      $nameFileN = 'publicaciones_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '_original.pdf';

      $qrUrl =  env('URL_CONSTANCIAS') . "constancias/" . $nameFile;
      $qrCode = base64_encode(QrCode::format('png')->size(300)->generate($qrUrl));

      $pdf = Pdf::loadView('investigador.constancias.publicacionesCientificasPDF', [
        'docente' => $docente,
        'publicaciones' => $publicaciones,
        'patentes' => $patentes,
        'username' => false,
        'file' => $nameFile,
        'qrCode' => $qrCode
      ]);

      return ['file' => $pdf->output(), 'name' => $nameFile, 'original_file' => $nameFileN];
    }
  }

  public function getConstanciaGrupoInvestigacion(Request $request, $solicitud = false) {
    $grupo = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', 'b.investigador_id', '=', 'a.id')
      ->join('Grupo AS c', 'c.id', '=', 'b.grupo_id')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->select(
        DB::raw('CONCAT(a.apellido1, " ", a.apellido2, " ", a.nombres) AS nombre'),
        'd.nombre AS facultad',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
        'a.doc_numero',
        'a.tipo',
        'b.cargo',
        'b.condicion',
        'c.grupo_nombre_corto',
        'c.grupo_nombre',
        'c.resolucion_rectoral',
        'c.resolucion_creacion_fecha'
      )
      ->where('a.id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->where('c.estado', '=', 4)
      ->where('b.condicion', 'not like', 'Ex %') // Excluir los que comienzan con "Ex "
      ->get()
      ->toArray();

    if (!$solicitud) {
      $pdf = Pdf::loadView('admin.constancias.grupoInvestigacionPDF', [
        'grupo' => $grupo,
        'username' => false
      ]);

      return $pdf->stream();
    } else {
      $date = Carbon::now();
      $nameFile = 'grupo_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '.pdf';
      $nameFileN = 'grupo_' . $request->attributes->get('token_decoded')->investigador_id . '_' . $date->format('YmdHis') . '_' . Str::random(8) . '_original.pdf';

      $qrUrl =  env('URL_CONSTANCIAS') . "constancias/" . $nameFile;
      $qrCode = base64_encode(QrCode::format('png')->size(300)->generate($qrUrl));

      $pdf = Pdf::loadView('investigador.constancias.grupoInvestigacionPDF', [
        'grupo' => $grupo,
        'username' => false,
        'file' => $nameFile,
        'qrCode' => $qrCode
      ]);

      return ['file' => $pdf->output(), 'name' => $nameFile, 'original_file' => $nameFileN];
    }
  }

  public function solicitarConstancia(Request $request) {
    $pdf = "";
    switch ($request->input('tipo')) {
      case "11":
        $pdf = $this->getConstanciaTesisAsesoria($request, true);
        break;
      case "1":
        $pdf = $this->getConstanciaEstudiosInvestigacion($request, true);
        break;
      case "7":
        $pdf = $this->getConstanciaEquipamientoCientifico($request, true);
        break;
      case "10":
        $pdf = $this->getConstanciaNoDeuda($request, true);
        break;
      case "4":
        $pdf = $this->getConstanciaPuntajePublicaciones($request, true);
        break;
      case "6":
        $pdf = $this->getConstanciaCapituloLibro($request, true);
        break;
      case "3":
        $pdf = $this->getConstanciaPublicacionesCientificas($request, true);
        break;
      case "2":
        $pdf = $this->getConstanciaGrupoInvestigacion($request, true);
        break;
      default:
        break;
    }

    $this->loadFile($pdf["file"], 'constancias', $pdf["name"]);

    DB::table('Constancia')
      ->insert([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'tipo' => $request->input('tipo_desc'),
        'archivo_firmado' => $pdf["name"],
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);

    $investigador = DB::table('Usuario_investigador')
      ->select([
        DB::raw("CONCAT(apellido1, ' ', apellido2, ', ', nombres) AS nombres"),
        'email3'
      ])
      ->where('id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->first();

    $file = $this->getFile('constancias', $pdf["name"]);

    Mail::to($investigador->email3)->send(new ConstanciaFirmada(
      $investigador->nombres,
      $request->input('tipo_desc'),
      $file
    ));

    return ['message' => 'success', 'detail' => 'Constancia emitida con éxito'];
  }
}
