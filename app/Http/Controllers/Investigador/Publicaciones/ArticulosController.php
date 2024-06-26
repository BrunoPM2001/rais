<?php

namespace App\Http\Controllers\Investigador\Publicaciones;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ArticulosController extends S3Controller {

  public function listado(Request $request) {
    $publicaciones = DB::table('Publicacion AS a')
      ->leftJoin('Publicacion_autor AS b', 'b.publicacion_id', '=', 'a.id')
      ->leftJoin('Publicacion_revista AS c', 'c.issn', '=', 'a.issn')
      ->select(
        'a.id',
        'a.titulo',
        DB::raw("IF(a.publicacion_nombre IS NULL OR a.publicacion_nombre = '',CONCAT(c.revista,' ',c.issn),CONCAT(a.publicacion_nombre,' ',a.issn)) AS revista"),
        'a.observaciones_usuario',
        DB::raw('YEAR(a.fecha_publicacion) AS año_publicacion'),
        'b.puntaje',
        'a.estado',
        'a.step'
      )
      ->where('a.estado', '>', 0)
      ->where('b.investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->whereIn('a.tipo_publicacion', ['articulo'])
      ->orderByDesc('a.updated_at')
      ->groupBy('a.id')
      ->get();

    return ['data' => $publicaciones];
  }

  public function registrarPaso1(Request $request) {
    if ($request->input('publicacion_id') == null) {
      $publicacion_id = DB::table('Publicacion')->insertGetId([
        'doi' => $request->input('doi'),
        'art_tipo' => $request->input('art_tipo')["value"],
        'titulo' => $request->input('titulo'),
        'pagina_inicial' => $request->input('pagina_inicial'),
        'pagina_final' => $request->input('pagina_final'),
        'fecha_publicacion' => $request->input('fecha_publicacion'),
        'publicacion_nombre' => $request->input('publicacion_nombre'),
        'issn' => $request->input('issn'),
        'issn_e' => $request->input('issn_e'),
        'volumen' => $request->input('volumen'),
        'edicion' => $request->input('edicion'),
        'url' => $request->input('url'),
        'validado' => 0,
        'step' => 1,
        'tipo_publicacion' => 'articulo',
        'estado' => 6,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

      DB::table('Publicacion_autor')->insert([
        'publicacion_id' => $publicacion_id,
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'tipo' => 'interno',
        'categoria' => 'Autor',
        'presentado' => 1,
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);

      foreach ($request->input('palabras_clave') as $palabra) {
        DB::table('Publicacion_palabra_clave')->insert([
          'publicacion_id' => $publicacion_id,
          'clave' => $palabra["label"]
        ]);
      }

      foreach ($request->input('indexada') as $indexada) {
        DB::table('Publicacion_index')->insert([
          'publicacion_id' => $publicacion_id,
          'publicacion_db_indexada_id' => $indexada["value"],
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
      }
      return ['message' => 'success', 'detail' => 'Datos de la publicación registrados', 'publicacion_id' => $publicacion_id];
    } else {
      $publicacion_id = $request->input('publicacion_id');
      $count = DB::table('Publicacion')
        ->where('id', '=', $publicacion_id)
        ->where('estado', '!=', -1)
        ->where('estado', '!=', 1)
        ->where('estado', '!=', 5)
        ->where('estado', '!=', 7)
        ->where('estado', '!=', 8)
        ->where('estado', '!=', 9)
        ->update([
          'doi' => $request->input('doi'),
          'art_tipo' => $request->input('art_tipo')["value"],
          'titulo' => $request->input('titulo'),
          'pagina_inicial' => $request->input('pagina_inicial'),
          'pagina_final' => $request->input('pagina_final'),
          'fecha_publicacion' => $request->input('fecha_publicacion'),
          'publicacion_nombre' => $request->input('publicacion_nombre'),
          'issn' => $request->input('issn'),
          'issn_e' => $request->input('issn_e'),
          'volumen' => $request->input('volumen'),
          'edicion' => $request->input('edicion'),
          'url' => $request->input('url'),
          'validado' => 0,
          'tipo_publicacion' => 'articulo',
          'estado' => 6,
          'updated_at' => Carbon::now()
        ]);

      DB::table('Publicacion')
        ->where('id', '=', $publicacion_id)
        ->where('estado', '!=', 5)
        ->update([
          'step' => 2
        ]);

      if ($count == 0) {
        return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
      }

      DB::table('Publicacion_palabra_clave')
        ->where('publicacion_id', '=', $publicacion_id)
        ->delete();
      foreach ($request->input('palabras_clave') as $palabra) {
        DB::table('Publicacion_palabra_clave')->insert([
          'publicacion_id' => $publicacion_id,
          'clave' => $palabra["label"]
        ]);
      }

      DB::table('Publicacion_index')
        ->where('publicacion_id', '=', $publicacion_id)
        ->delete();
      foreach ($request->input('indexada') as $indexada) {
        DB::table('Publicacion_index')->insert([
          'publicacion_id' => $publicacion_id,
          'publicacion_db_indexada_id' => $indexada["value"],
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ]);
      }
      return ['message' => 'success', 'detail' => 'Datos de la publicación actualizados'];
    }
  }

  public function datosPaso1(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      $publicacion = DB::table('Publicacion')
        ->select([
          'id',
          'doi',
          'art_tipo',
          'titulo',
          'pagina_inicial',
          'pagina_final',
          'fecha_publicacion',
          'publicacion_nombre',
          'issn',
          'issn_e',
          'volumen',
          'edicion',
          'url',
        ])
        ->where('id', '=', $request->query('publicacion_id'))
        ->first();

      $palabras_clave = DB::table('Publicacion_palabra_clave')
        ->select([
          'clave AS label'
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      $indexada = DB::table('Publicacion_index AS a')
        ->join('Publicacion_db_indexada AS b', 'b.id', '=', 'a.publicacion_db_indexada_id')
        ->select([
          'b.id AS value',
          'b.nombre AS label'
        ])
        ->where('a.publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      $revistas =  $this->listadoRevistasIndexadas();

      return [
        'data' => $publicacion,
        'palabras_clave' => $palabras_clave,
        'indexada' => $indexada,
        'revistas' => $revistas
      ];
    } else {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  //  TODO - Poner todo en un nuevo controlador
  public function listadoRevistasIndexadas() {
    $revistas = DB::table('Publicacion_db_indexada')
      ->select(
        'id AS value',
        'nombre AS label',
      )
      ->where('estado', '!=', 0)
      ->get();

    return $revistas;
  }

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
      ->having('value', 'LIKE', '%' . $request->query('query') . '%')
      ->limit(10)
      ->get();

    return $proyectos;
  }

  public function agregarProyecto(Request $request) {

    $count = DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->where('estado', '!=', 5)
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    if ($request->input('proyecto_id') != null) {
      DB::table('Publicacion_proyecto')->insert([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'publicacion_id' => $request->input('publicacion_id'),
        'proyecto_id' => $request->input('proyecto_id'),
        'codigo_proyecto' => $request->input('codigo_proyecto'),
        'nombre_proyecto' => $request->input('nombre_proyecto'),
        'entidad_financiadora' => $request->input('entidad_financiadora'),
        'tipo' => 'INTERNO',
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);
    } else {
      DB::table('Publicacion_proyecto')->insert([
        'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
        'publicacion_id' => $request->input('publicacion_id'),
        'codigo_proyecto' => $request->input('codigo_proyecto'),
        'nombre_proyecto' => $request->input('nombre_proyecto'),
        'entidad_financiadora' => $request->input('entidad_financiadora'),
        'tipo' => 'EXTERNO',
        'estado' => 1,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ]);
    }

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
      ->where('estado', '!=', 5)
      ->delete();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    return ['message' => 'info', 'detail' => 'Proyecto eliminado de la lista exitosamente'];
  }

  public function listarAutores(Request $request) {
    $esAutor = DB::table('Publicacion_autor')
      ->where('publicacion_id', '=', $request->query('publicacion_id'))
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($esAutor > 0) {
      $proyectos = DB::table('Publicacion_autor AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select([
          'a.id',
          'a.presentado',
          'a.categoria',
          'a.autor',
          'b.tipo',
          DB::raw("CONCAT(b.apellido1, ' ', b.apellido2, ', ', b.nombres) AS nombres"),
          'a.filiacion',
        ])
        ->where('publicacion_id', '=', $request->query('publicacion_id'))
        ->get();

      return $proyectos;
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
    $investigadores = DB::table('Usuario_investigador')
      ->select(
        DB::raw("CONCAT(doc_numero, ' | ', codigo, ' | ', apellido1, ' ', apellido2, ' ', nombres) AS value"),
        'id',
        'nombres',
        'apellido1',
        'apellido2',
        'tipo'
      )
      ->where('tipo', 'LIKE', 'ESTUDIANTE%')
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
      ->where('estado', '!=', 5)
      ->count();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    DB::table('Publicacion_autor')->insert([
      'publicacion_id' => $request->input('publicacion_id'),
      'investigador_id' => $request->input('investigador_id'),
      'tipo' => $request->input('tipo'),
      'autor' => $request->input('autor'),
      'categoria' => $request->input('categoria'),
      'filiacion' => $request->input('filiacion'),
      'presentado' => 0,
      'estado' => 0,
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now()
    ]);

    DB::table('Publicacion')
      ->where('id', '=', $request->input('publicacion_id'))
      ->where('estado', '!=', 5)
      ->update([
        'step' => 3
      ]);

    return ['message' => 'success', 'detail' => 'Autor agregado exitosamente'];
  }

  public function editarAutor(Request $request) {
    $count = DB::table('Publicacion_autor')
      ->where('id', '=', $request->input('id'))
      ->where('estado', '=', 5)
      ->update([
        'autor' => $request->input('autor'),
        'categoria' => $request->input('categoria'),
        'filiacion' => $request->input('filiacion'),
        'updated_at' => Carbon::now()
      ]);

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    return ['message' => 'info', 'detail' => 'Datos del autor editado exitosamente'];
  }

  public function eliminarAutor(Request $request) {
    $count = DB::table('Publicacion_autor')
      ->where('estado', '=', 5)
      ->where('id', '=', $request->query('id'))
      ->delete();

    if ($count == 0) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    return ['message' => 'info', 'detail' => 'Autor eliminado de la lista exitosamente'];
  }

  public function enviarPublicacion(Request $request) {
    $publicacion = DB::transaction(function () use ($request) {
      $count = DB::table('Publicacion')
        ->where('id', '=', $request->input('publicacion_id'))
        ->where('estado', '!=', '5')
        ->update([
          'step' => 4,
          'estado' => 5
        ]);

      if ($count == 0) {
        return null;
      }

      $publicacion = DB::table('Publicacion')
        ->select([
          'tipo_publicacion',
          'estado'
        ])
        ->where('id', '=', $request->input('publicacion_id'))
        ->first();

      return $publicacion;
    });

    if ($publicacion == null) {
      return ['message' => 'error', 'detail' => 'Esta publicación ya ha sido enviada, no se pueden hacer más cambios'];
    }

    if ($publicacion->tipo_publicacion == "articulo") {
      $validator = Validator::make($request->allFiles(), [
        'file' => 'required|file|max:6144',
      ]);

      if ($validator->fails()) {
        return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
      }
    }

    if ($request->hasFile('file')) {
      $this->uploadFile($request->file('file'), "publicacion", $request->input('publicacion_id'));
    }
    return ['message' => 'success', 'detail' => 'Publicación enviada correctamente'];
  }
}
