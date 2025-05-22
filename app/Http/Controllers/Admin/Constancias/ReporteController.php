<?php

namespace App\Http\Controllers\Admin\Constancias;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller {

  public function searchConstanciaBy(Request $request) {
    $docente = DB::table('Usuario_investigador AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.investigador_id', '=', 'a.id')
      ->select(
        DB::raw("
                  CONCAT(
                      TRIM(a.codigo), ' | ', 
                      a.doc_numero, ' | ', 
                      a.apellido1, ' ', a.apellido2, ', ', a.nombres, ' | ', 
                      COALESCE(a.tipo, CONCAT(a.tipo_investigador, ' - ', a.tipo_investigador_estado))
                  ) AS value
              "),
        'a.id AS investigador_id',
        'a.id',
        'a.codigo',
        'a.doc_numero',
        'a.apellido1',
        'a.apellido2',
        'a.nombres',
        'a.tipo',
        'a.tipo_investigador',
        'a.tipo_investigador_estado',
        DB::raw("COUNT(b.id) AS publicaciones")
      )
      ->whereRaw('LOWER(a.tipo_investigador) LIKE ?', ['docente%'])
      ->orWhereRaw('LOWER(a.tipo) LIKE ?', ['docente%'])
      ->orWhereRaw('LOWER(a.tipo_investigador) LIKE ?', ['estudiante%'])
      ->orWhereRaw('LOWER(a.tipo) LIKE ?', ['estudiante%'])
      ->orWhereRaw('LOWER(a.tipo_investigador) LIKE ?', ['externo%'])
      ->orWhereRaw('LOWER(a.tipo) LIKE ?', ['externo%'])
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->groupBy('a.id')
      ->limit(10)
      ->get();

    return $docente;
  }

  public function  checkTiposConstancia($investigador_id) {

    $estudio = $this->getConstanciaEstudiosInvestigacion($investigador_id);
    $tesis = $this->getConstanciaTesisAsesoria($investigador_id);
    $equipamiento = $this->getConstanciaEquipamientoCientifico($investigador_id);
    $deuda = $this->getConstanciaNoDeuda($investigador_id);
    $puntaje = $this->getConstanciaPuntajePublicaciones($investigador_id);
    $capitulo = $this->getConstanciaCapituloLibro($investigador_id);
    $publicaciones = $this->getConstanciaPublicacionesCientificas($investigador_id);
    $grupo = $this->getConstanciaGrupoInvestigacion($investigador_id);


    return [
      'estudio' => $estudio,
      'tesis' => $tesis,
      'equipamiento' => $equipamiento,
      'deuda' => $deuda,
      'puntaje' => $puntaje,
      'capitulo' => $capitulo,
      'publicaciones' => $publicaciones,
      'grupo' => $grupo
    ];
  }

  public function getDatosDocente($investigador_id) {
    $docente = DB::table('Usuario_investigador AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoin('Repo_rrhh AS rrhh', 'rrhh.ser_doc_id_act', '=', 'a.doc_numero')
      ->leftJoin('Docente_categoria AS c', 'rrhh.ser_cat_act', '=', 'c.categoria_id')
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
      ->where('a.id', '=', $investigador_id)
      ->get()
      ->toArray();

    return $docente;
  }


  public function getConstanciaTesisAsesoria(Request $request) {
    $docente = $this->getDatosDocente($request->query('investigador_id'));

    $tesis = DB::table('view_proyecto_reporte AS a')
      ->select(
        '*',

      )
      ->where('a.investigador_id', '=', $request->query('investigador_id'))
      ->whereIn('a.tipo_proyecto', ['PTPBACHILLER', 'PTPMAEST', 'PTPDOCTO', 'PTPGRADO', 'Tesis'])
      ->orderBy('a.periodo', 'DESC')
      ->get();


    $pdf = Pdf::loadView('admin.constancias.tesisAsesoriaPDF', [
      'docente' => $docente[0],
      'tesis' => $tesis,
      'username' => $request->attributes->get('token_decoded')->username
    ]);
    return $pdf->stream();
  }

  public function getConstanciaEstudiosInvestigacion(Request $request) {

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

    $docente = $this->getDatosDocente($request->query('investigador_id'));

    $proyectos = DB::table('view_proyecto_reporte as vreport')
      ->select('*')
      ->where('vreport.investigador_id', '=', $request->query('investigador_id'))
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


    $pdf = Pdf::loadView('admin.constancias.estudiosInvestigacionPDF', [
      'docente' => $docente[0], // Pasar el docente
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
      'username' => $request->attributes->get('token_decoded')->username
    ]);

    // Retornar el PDF generado (puedes usar `stream` o `download`)
    return $pdf->stream();
  }

  public function getConstanciaEquipamientoCientifico(Request $request) {
    $docente = $this->getDatosDocente($request->query('investigador_id'));

    $equipamiento = DB::table('view_proyecto_reporte AS a')
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
      ->join('view_proyecto_presupuesto AS p', 'p.proyecto_id', '=', 'a.proyecto_id')
      ->where('a.tipo_proyecto', '=', 'ECI')
      ->where('a.investigador_id', '=', $request->query('investigador_id'))
      ->orderBy('a.periodo', 'DESC')
      ->get();

    $pdf = Pdf::loadView('admin.constancias.equipamientoPDF', [
      'docente' => $docente[0],
      'equipamiento' => $equipamiento,
      'username' => $request->attributes->get('token_decoded')->username
    ]);
    return $pdf->stream();
  }

  public function getConstanciaNoDeuda(Request $request) {
    $docente = $this->getDatosDocente($request->query('investigador_id'));

    $deudores = DB::table('view_deudores')
      ->select(
        '*',
      )
      ->where('investigador_id', '=', $request->query('investigador_id'))
      ->get();

    $deuda = count($deudores);


    $pdf = Pdf::loadView('admin.constancias.noDeudaPDF', [
      'docente' => $docente[0],
      'deuda' => $deuda,
      'username' => $request->attributes->get('token_decoded')->username
    ]);
    return $pdf->stream();
  }

  //  TODO - Ver por qué la suma de puntos no coincide
  public function getConstanciaPuntajePublicaciones(Request $request) {

    $docente = $this->getDatosDocente($request->query('investigador_id'));

    $publicaciones = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->join('Publicacion_categoria AS c', 'c.id', '=', 'b.categoria_id')
      ->select(
        'c.titulo',
        'c.categoria',
        DB::raw('COUNT(*) AS cantidad'),
        DB::raw('(a.puntaje * COUNT(*)) AS puntaje')
      )
      ->where('a.investigador_id', '=', $request->query('investigador_id'))
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
      ->where('b.investigador_id', $request->query('investigador_id'))
      ->groupBy('a.tipo') // Agrupación solo por tipo
      ->orderBy('a.tipo') // Ordenar por tipo
      ->get()
      ->toArray();

    $pdf = Pdf::loadView('admin.constancias.puntajePublicacionesPDF', [
      'docente' => $docente[0],
      'publicaciones' => $publicaciones,
      'patentes' => $patentes,
      'username' => $request->attributes->get('token_decoded')->username
    ]);
    return $pdf->stream();
  }

  public function getConstanciaCapituloLibro(Request $request) {

    $docente = $this->getDatosDocente($request->query('investigador_id'));

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
      ->where('b.investigador_id', $request->query('investigador_id'))
      ->whereIn('a.tipo_publicacion', ['libro', 'capitulo'])
      ->orderByRaw('6 DESC, 3 DESC, 4') // Ordena por las posiciones en SELECT
      ->get();



    $pdf = Pdf::loadView('admin.constancias.capituloLibroPDF', [
      'docente' => $docente[0],
      'publicaciones' => $publicaciones,
      'username' => $request->attributes->get('token_decoded')->username
    ]);
    return $pdf->stream();
  }
  //  TODO - Verificar que las observaciones sean de esa columna
  public function getConstanciaPublicacionesCientificas(Request $request) {
    $docente = $this->getDatosDocente($request->query('investigador_id'));

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
      ->where('a.investigador_id', '=', $request->query('investigador_id'))
      ->where('b.validado', '=', 1)
      ->orderBy('c.tipo') // Ordenar por tipo de publicación
      ->orderBy('c.categoria') // Luego por categoría
      ->orderByDesc('año') // Después por año, de forma descendente
      ->orderBy('b.titulo') // Finalmente, por título de publicación
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
      ->where('b.investigador_id', '=', $request->query('investigador_id'))
      ->orderBy('a.tipo') // Ordenar por tipo de publicación
      ->orderByDesc('c.updated_at') // Después por año, de forma descendente
      ->orderBy('a.titulo') // Finalmente, por título de publicación
      ->get();


    $pdf = Pdf::loadView('admin.constancias.publicacionesCientificasPDF', [
      'docente' => $docente[0],
      'publicaciones' => $publicaciones,
      'patentes' => $patentes,
      'username' => $request->attributes->get('token_decoded')->username
    ]);

    return $pdf->stream();
  }


  public function getConstanciaGrupoInvestigacion(Request $request) {
    $grupo = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', 'b.investigador_id', '=', 'a.id')
      ->join('Grupo AS c', 'c.id', '=', 'b.grupo_id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
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
      ->where('a.id', '=', $request->query('investigador_id'))
      ->where('c.estado', '=', 4)
      ->where('b.condicion', 'not like', 'Ex %') // Excluir los que comienzan con "Ex "
      ->get()
      ->toArray();

    $pdf = Pdf::loadView('admin.constancias.grupoInvestigacionPDF', [
      'grupo' => $grupo,
      'username' => $request->attributes->get('token_decoded')->username
    ]);
    return $pdf->stream();
  }
}
