<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Illuminate\Support\Facades\DB;

class GruposController extends S3Controller {

  public function listadoGrupos() {
    //  Subquery para obtener el coordinador de cada grupo
    $coordinador = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.grupo_id AS id',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombre'),
      )
      ->where('a.cargo', '=', 'Coordinador');

    $grupos = DB::table('Grupo AS a')
      ->join('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.grupo_nombre',
        'a.grupo_nombre_corto',
        'a.grupo_categoria',
        DB::raw('COUNT(b.id) AS cantidad_integrantes'),
        'd.nombre AS facultad',
        'coordinador.nombre AS coordinador',
        'a.resolucion_rectoral',
        'a.created_at',
        'a.updated_at',
        'a.estado'
      )
      ->leftJoinSub($coordinador, 'coordinador', 'coordinador.id', '=', 'a.id')
      ->where('a.tipo', '=', 'grupo')
      ->groupBy('a.id')
      ->get();

    return ['data' => $grupos];
  }

  public function listadoSolicitudes() {
    //  Subquery para obtener el coordinador de cada grupo
    $coordinador = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.grupo_id AS id',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombre'),
      )
      ->where('a.cargo', '=', 'Coordinador');

    //  Query base
    $solicitudes = DB::table('Grupo AS a')
      ->leftJoin('Grupo_integrante AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Facultad AS d', 'd.id', '=', 'a.facultad_id')
      ->leftJoinSub($coordinador, 'coordinador', 'coordinador.id', '=', 'a.id')
      ->select(
        'a.id',
        'a.grupo_nombre',
        'a.grupo_nombre_corto',
        DB::raw('COUNT(b.id) AS cantidad_integrantes'),
        'd.nombre AS facultad',
        'coordinador.nombre AS coordinador',
        'a.estado',
        'a.created_at',
        'a.updated_at'
      )
      ->where('a.tipo', '=', 'solicitud')
      ->havingBetween('a.estado', [0, 6])
      ->groupBy('a.id')
      ->get();

    return ['data' => $solicitudes];
  }

  public function detalle($grupo_id) {

    $s3 = $this->s3Client;

    $coordinador = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->select(
        'a.grupo_id AS id',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombre'),
      )
      ->where('a.cargo', '=', 'Coordinador');

    $detalleGrupo = DB::table('Grupo AS a')
      ->join('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->leftJoinSub($coordinador, 'coordinador', 'coordinador.id', '=', 'a.id')
      ->select(
        'a.id',
        'a.grupo_nombre',
        'a.resolucion_rectoral_creacion',
        'a.resolucion_creacion_fecha',
        'a.resolucion_rectoral',
        'a.resolucion_fecha',
        'a.observaciones',
        'a.observaciones_admin',
        'coordinador.nombre AS coordinador',
        'a.estado',
        'b.nombre AS facultad',
        'a.telefono',
        'a.anexo',
        'a.oficina',
        'a.direccion',
        'a.email',
        'a.web',
        'a.grupo_categoria',
        'a.presentacion',
        'a.objetivos',
        'a.servicios',
        'a.infraestructura_ambientes',
        'a.infraestructura_sgestion'
      )
      ->where('a.id', '=', $grupo_id)
      ->get();

    //  Obtener objetos del bucket
    foreach ($detalleGrupo as $detalle) {
      $url = null;
      if ($detalle->infraestructura_sgestion != null) {
        $cmd = $s3->getCommand('GetObject', [
          'Bucket' => 'grupo-infraestructura-sgestion',
          'Key' => $detalle->id . "." . $detalle->infraestructura_sgestion
        ]);
        //  Generar url temporal
        $url = (string) $s3->createPresignedRequest($cmd, '+10 minutes')->getUri();
      }
      $detalle->infraestructura_sgestion = $url;
    }
    return ['data' => $detalleGrupo];
  }

  public function miembros($grupo_id, $estado) {
    $miembros = DB::table('Grupo_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->join('Facultad AS c', 'c.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_integrante AS d', 'd.grupo_integrante_id', '=', 'a.id')
      ->select(
        'a.id',
        'a.investigador_id',
        'a.condicion',
        'a.cargo',
        'b.doc_numero',
        DB::raw('CONCAT(b.apellido1, " ", b.apellido2, ", ", b.nombres) AS nombres'),
        'b.codigo_orcid',
        'b.google_scholar',
        'b.cti_vitae',
        'b.tipo',
        'c.nombre AS facultad',
        'a.tesista',
        DB::raw('COUNT(d.id) AS proyectos'),
        'a.fecha_inclusion',
        'a.fecha_exclusion'
      )
      ->where('a.grupo_id', '=', $grupo_id);

    //  Tipo de miembro
    $miembros = $estado == 1 ? $miembros->whereNull('a.fecha_exclusion') : $miembros->whereNotNull('a.fecha_exclusion');

    $miembros = $miembros->groupBy('a.id')
      ->get();

    return ['data' => $miembros];
  }

  public function docs($grupo_id) {
    $docs = DB::table('Grupo_integrante_doc')
      ->select(
        'id',
        'nombre',
        'archivo_tipo',
        'fecha'
      )
      ->where('grupo_id', '=', $grupo_id)
      ->get();

    return ['data' => $docs];
  }

  public function lineas($grupo_id) {
    $lineas = DB::table('Grupo_linea AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->join('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->select(
        'a.id',
        'c.codigo',
        'c.nombre'
      )
      ->whereNull('a.concytec_codigo')
      ->where('a.grupo_id', '=', $grupo_id)
      ->get();

    return ['data' => $lineas];
  }

  public function proyectos($grupo_id) {
    //  TODO - Averiguar los criterios para colocar un proyecto como válido
    $proyectos = DB::table('Proyecto')
      ->select(
        'id',
        'titulo',
        'periodo',
        'tipo_proyecto'
      )
      ->where('grupo_id', '=', $grupo_id)
      ->get();

    return ['data' => $proyectos];
  }

  public function publicaciones($grupo_id) {
    $publicaciones = DB::table('Grupo_integrante AS a')
      ->join('Publicacion_autor AS b', 'b.investigador_id', '=', 'a.investigador_id')
      ->join('Publicacion AS c', 'c.id', '=', 'b.publicacion_id')
      ->join('Publicacion_categoria AS d', 'd.id', '=', 'c.categoria_id')
      ->select(
        'c.id',
        'c.titulo',
        'c.fecha_publicacion',
        'd.tipo'
      )
      ->where('a.grupo_id', '=', $grupo_id)
      ->groupBy('c.id')
      ->get();

    return ['data' => $publicaciones];
  }

  public function laboratorios($grupo_id) {
    $laboratorios = DB::table('Grupo AS a')
      ->join('Grupo_infraestructura AS b', 'b.grupo_id', '=', 'a.id')
      ->join('Laboratorio AS c', 'c.id', '=', 'b.laboratorio_id')
      ->select(
        'c.codigo',
        'c.laboratorio',
        'c.ubicacion',
        'c.responsable'
      )
      ->where('a.id', '=', $grupo_id)
      ->where('b.categoria', '=', 'laboratorio')
      ->get();

    return ['data' => $laboratorios];
  }

  //  TODO - implementar reporte de calificación e imprimir

  public function main() {
    return view("admin.estudios.gestion_grupos");
  }
}
