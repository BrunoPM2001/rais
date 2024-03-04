<?php

namespace App\Http\Controllers\Admin\Constancias;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller {

  public function getConstanciaPuntajePublicaciones($investigador_id) {
    $docente = DB::table('Usuario_investigador AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        DB::raw('CONCAT(a.apellido1, " ", a.apellido2, " ", a.nombres) AS nombre'),
        'b.nombre AS facultad'
      )
      ->where('a.id', '=', $investigador_id)
      ->get()
      ->toArray();

    $publicaciones = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->join('Publicacion_categoria AS c', 'c.id', '=', 'b.categoria_id')
      ->select(
        'c.titulo',
        'c.categoria',
        DB::raw('COUNT(*) AS cantidad'),
        DB::raw('(c.puntaje * COUNT(*)) AS puntaje')
      )
      ->where('a.investigador_id', '=', $investigador_id)
      ->where('b.estado', '=', 1)
      ->groupBy('b.categoria_id')
      ->groupBy('c.titulo')
      ->groupBy('c.categoria')
      ->orderBy('c.titulo')
      ->orderBy('c.categoria')
      ->get()
      ->toArray();

    $pdf = Pdf::loadView('admin.constancias.puntajePublicacionesPDF', ['docente' => $docente[0], 'publicaciones' => $publicaciones]);
    return $pdf->stream();
  }

  //  TODO - Verificar que las observaciones sean de esa columna
  public function getConstanciaPublicacionesCientificas($investigador_id) {
    $publicaciones = DB::table('Publicacion_autor AS a')
      ->join('Publicacion AS b', 'b.id', '=', 'a.publicacion_id')
      ->join('Publicacion_categoria AS c', 'c.id', '=', 'b.categoria_id')
      ->select(
        'c.tipo',
        'c.categoria',
        DB::raw('YEAR(b.fecha_publicacion) AS año'),
        'b.titulo',
        'b.publicacion_nombre',
        'b.issn',
        'b.isbn',
        'b.universidad',
        'b.pais',
        'b.observaciones_usuario',
      )
      ->where('a.investigador_id', '=', $investigador_id)
      ->where('b.estado', '=', 1)
      ->orderBy('c.tipo')
      ->orderBy('c.categoria')
      ->orderByDesc('año')
      ->get();

    return ['data' => $publicaciones];
  }

  public function getConstanciaGrupoInvestigacion($investigador_id) {
    $grupo = DB::table('Usuario_investigador AS a')
      ->join('Grupo_integrante AS b', 'b.investigador_id', '=', 'a.id')
      ->join('Grupo AS c', 'c.id', '=', 'b.grupo_id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->select(
        DB::raw('CONCAT(a.apellido1, " ", a.apellido2, " ", a.nombres) AS nombre'),
        'd.nombre AS facultad',
        'b.cargo',
        'b.condicion',
        'c.grupo_nombre_corto',
        'c.grupo_nombre',
        'c.resolucion_rectoral',
        'c.resolucion_creacion_fecha'
      )
      ->where('a.id', '=', $investigador_id)
      ->where('b.estado', '=', 1)
      ->get()
      ->toArray();

    $pdf = Pdf::loadView('admin.constancias.grupoInvestigacionPDF', ['grupo' => $grupo]);
    return $pdf->stream();
  }
}
