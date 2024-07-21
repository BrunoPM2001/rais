<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicacionesUtilsController extends S3Controller {

  /*
  |-----------------------------------------------------------
  | Pasos 2, 3 y 4
  |-----------------------------------------------------------
  |
  | Funciones para los pasos 2, 3 y 4 de cada publicación, ya
  | que estos se repiten.
  |
  */

  //  Paso 2
  public function proyectos_asociados(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      $proyectos = DB::table('Publicacion_proyecto')
        ->select([
          'id',
          'codigo_proyecto',
          'nombre_proyecto',
          'entidad_financiadora',
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      return $proyectos;
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  public function proyectos_registrados(Request $request) {
    $proyectos = DB::table('Proyecto AS a')
      ->leftJoin('Proyecto_descripcion AS b', function ($join) {
        $join->on('b.proyecto_id', '=', 'a.id')
          ->where('b.codigo', '=', 'fuente_financiadora');
      })
      ->select(
        DB::raw("CONCAT(a.codigo_proyecto, ' | ', a.titulo) AS value"),
        'a.id AS proyecto_id',
        'a.codigo_proyecto',
        'a.titulo',
        DB::raw("IFNULL(b.detalle, 'UNMSM') AS entidad_financiadora")
      )
      ->whereNotNull('codigo_proyecto')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $proyectos;
  }

  public function agregarProyecto(Request $request) {
    $count = DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    DB::table('Publicacion_proyecto')
      ->insert([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'publicacion_id' => $request->input('publicacion_id'),
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo_proyecto' => $request->input('codigo_proyecto'),
        'nombre_proyecto' => $request->input('nombre_proyecto'),
        'entidad_financiadora' => $request->input('entidad_financiadora'),
        'tipo' => $request->input('proyecto_id') == null ? 'EXTERNO' : 'INTERNO',
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);


    DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->update([
        'step' => 2
      ]);

    return ['message' => 'success', 'detail' => 'Proyecto agregado exitosamente'];
  }

  public function eliminarProyecto(Request $request) {
    $count = DB::table('Publicacion_proyecto')
      ->where('id', '=', $request->query('proyecto_id'))
      ->whereIn('estado', [2, 6])
      ->delete();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    return ['message' => 'info', 'detail' => 'Proyecto eliminado de la lista exitosamente'];
  }

  //  Paso 3
  public function listarAutores(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      $autores = DB::table('Publicacion_autor AS a')
        ->leftJoin('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select([
          'a.id',
          'a.presentado',
          'a.categoria',
          'a.autor',
          DB::raw("COALESCE(b.tipo, 'Externo') AS tipo"),
          DB::raw("COALESCE(CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres), 
                  CONCAT(a.apellido1, ' ', a.apellido2, ', ', a.nombres)) AS nombres"),
          'a.filiacion',
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      return $autores;
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  public function searchDocenteRegistrado(Request $request) {
    $investigadores = DB::table('Usuario_investigador')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', codigo, ' | ', apellido1, ' ', apellido2, ' ', nombres) AS value"),
        'id',
        'nombres',
        'apellido1',
        'apellido2',
        'tipo'
      )
      ->where('tipo', 'LIKE', 'DOCENTE%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function searchEstudianteRegistrado(Request $request) {
    $investigadores = DB::table('Repo_sum AS a')
      ->leftJoin('Usuario_investigador AS b', 'b.codigo', '=', 'a.codigo_alumno')
      ->select(
        DB::raw("CONCAT(TRIM(a.codigo_alumno), ' | ', a.dni, ' | ', a.apellido_paterno, ' ', a.apellido_materno, ', ', a.nombres, ' | ', a.programa) AS value"),
        'a.id',
        'b.id AS investigador_id',
        'a.codigo_alumno',
        'a.apellido_paterno',
        'a.apellido_materno',
        'a.nombres',
        'a.programa',
      )
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function searchExternoRegistrado(Request $request) {
    $investigadores = DB::table('Usuario_investigador')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', codigo, ' | ', apellido1, ' ', apellido2, ' ', nombres) AS value"),
        'id',
        'nombres',
        'apellido1',
        'apellido2',
        'tipo'
      )
      ->where('tipo', 'LIKE', 'EXTERNO%')
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $investigadores;
  }

  public function agregarAutor(Request $request) {

    $count = DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    switch ($request->input('tipo')) {
      case "externo":
        DB::table('Publicacion_autor')->insert([
          'publicacion_id' => $request->input('publicacion_id'),
          'tipo' => $request->input('tipo'),
          'nombres' => $request->input('nombres'),
          'apellido1' => $request->input('apellido1'),
          'apellido2' => $request->input('apellido2'),
          'autor' => $request->input('autor'),
          'categoria' => $request->input('categoria'),
          'filiacion' => $request->input('filiacion'),
          'presentado' => 0,
          'estado' => 0,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
        break;
      case "estudiante":
        $id_investigador = $request->input('investigador_id');

        if ($id_investigador == null) {
          $sumData = DB::table('Repo_sum')
            ->select([
              'id_facultad',
              'codigo_alumno',
              'nombres',
              'apellido_paterno',
              'apellido_materno',
              'dni',
              'sexo',
              'correo_electronico',
            ])
            ->where('id', '=', $request->input('sum_id'))
            ->first();

          $id_investigador = DB::table('Usuario_investigador')
            ->insertGetId([
              'facultad_id' => $sumData->id_facultad,
              'codigo' => $sumData->codigo_alumno,
              'nombres' => $sumData->nombres,
              'apellido1' => $sumData->apellido_paterno,
              'apellido2' => $sumData->apellido_materno,
              'doc_tipo' => 'DNI',
              'doc_numero' => $sumData->dni,
              'sexo' => $sumData->sexo,
              'email3' => $sumData->correo_electronico,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
              'tipo_investigador' => 'Estudiante',
              'tipo' => 'Estudiante'
            ]);
        }

        DB::table('Publicacion_autor')->insert([
          'publicacion_id' => $request->input('publicacion_id'),
          'investigador_id' => $id_investigador,
          'tipo' => "interno",
          'autor' => $request->input('autor'),
          'categoria' => $request->input('categoria'),
          'filiacion' => $request->input('filiacion'),
          'presentado' => 0,
          'estado' => 0,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
        break;
      case "interno":

        DB::table('Publicacion_autor')->insert([
          'publicacion_id' => $request->input('publicacion_id'),
          'investigador_id' => $request->input('investigador_id'),
          'tipo' => "interno",
          'autor' => $request->input('autor'),
          'categoria' => $request->input('categoria'),
          'filiacion' => $request->input('filiacion'),
          'presentado' => 0,
          'estado' => 0,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
        break;
      default:
        break;
    }

    DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->where('estado', '!=', 5)
      ->update([
        'step' => 3
      ]);

    return ['message' => 'success', 'detail' => 'Autor agregado exitosamente'];
  }

  public function editarAutor(Request $request) {
    $count = DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    DB::table('Publicacion_autor')
      ->where('id', '=', $request->input('id'))
      ->update([
        'autor' => $request->input('autor'),
        'categoria' => $request->input('categoria'),
        'filiacion' => $request->input('filiacion'),
        'updated_at' => Carbon::now()
      ]);

    return ['message' => 'info', 'detail' => 'Datos del autor editado exitosamente'];
  }

  public function eliminarAutor(Request $request) {
    $count = DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->whereIn('estado', [2, 6])
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    DB::table('Publicacion_autor')
      ->where('id', '=', $request->query('id'))
      ->delete();

    return ['message' => 'info', 'detail' => 'Autor eliminado de la lista exitosamente'];
  }

  //  Paso 4
  public function enviarPublicacion(Request $request) {
    if ($request->hasFile('file')) {
      $count = DB::table('Publicacion')
        ->where('id', '=', $request->input('publicacion_id'))
        ->whereIn('estado', [2, 6])
        ->update([
          'step' => 4,
          'estado' => 5
        ]);

      if ($count == 0) {
        return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
      } else {

        $date = Carbon::now();
        $name = "token-" . $date->format('Ymd-His') . "-" . Str::random(8);
        $nameFile = $name . "." . $request->file('file')->getClientOriginalExtension();

        $this->uploadFile($request->file('file'), "publicacion", $nameFile);

        return ['message' => 'success', 'detail' => 'Publicación enviada correctamente'];
      }
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar el archivo'];
    }
  }

  public function reporte(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      switch ($request->query('tipo')) {
        case "articulo":
          $util = new ArticulosController();
          return $util->reporte($request);
          break;
        case "libro":
          $util = new LibrosController();
          return $util->reporte($request);
          break;
        case "capitulo_libro":
          $util = new CapitulosLibrosController();
          return $util->reporte($request);
          break;
        case "tesis_propia":
          $util = new TesisPropiasController();
          return $util->reporte($request);
          break;
        case "tesis_asesoria":
          $util = new TesisAsesoriaController();
          return $util->reporte($request);
          break;
        case "evento":
          $util = new EventoController();
          return $util->reporte($request);
          break;
        default:
          break;
      }
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  /*
  |-----------------------------------------------------------
  | Listado de data
  |-----------------------------------------------------------
  |
  | Listado de revistas, países, etc. Usados al momento de 
  | registrar más de un tipo de controlador de publicación.
  |
  */

  public function listadoRevistasIndexadas() {
    $revistas = DB::table('Publicacion_db_indexada')
      ->select([
        'id AS value',
        'nombre AS label',
      ])
      ->where('estado', '!=', 0)
      ->get();

    return $revistas;
  }

  public function getPaises() {
    $paises = DB::table('Pais')
      ->select(['name AS value'])
      ->get();
    return $paises;
  }
}
