<?php

namespace App\Http\Controllers\Admin\Estudios;

use App\Http\Controllers\S3Controller;
use Illuminate\Support\Facades\DB;

class ProyectosGrupoController extends S3Controller {
  public function listado($periodo) {
    $proyectos = DB::table('Proyecto AS a')
      ->join('Grupo AS b', 'b.id', '=', 'a.grupo_id')
      ->join('Linea_investigacion AS c', 'c.id', '=', 'a.linea_investigacion_id')
      ->leftJoin('Proyecto_integrante AS d', 'd.proyecto_id', '=', 'a.id')
      ->join('Facultad AS e', 'e.id', '=', 'b.facultad_id')
      ->leftJoin('Proyecto_presupuesto AS f', 'f.proyecto_id', '=', 'a.id')
      ->join('Usuario_investigador AS g', 'g.id', '=', 'd.investigador_id')
      ->select(
        'a.id',
        'a.tipo_proyecto',
        'a.codigo_proyecto',
        'c.nombre AS linea',
        'a.titulo',
        DB::raw('CONCAT(g.apellido1, " " , g.apellido2, ", ", g.nombres) AS responsable'),
        'b.grupo_nombre',
        'e.nombre AS facultad',
        DB::raw('SUM(f.monto) AS monto'),
        'a.resolucion_rectoral',
        'a.updated_at',
        'a.estado'
      )
      ->where('d.condicion', '=', 'Responsable')
      ->where('a.periodo', '=', $periodo)
      ->groupBy('a.id')
      ->get();

    return ['data' => $proyectos];
  }

  public function detalle($proyecto_id) {
    $detalle = DB::table('Proyecto AS a')
      ->join('Linea_investigacion AS b', 'b.id', '=', 'a.linea_investigacion_id')
      ->join('Ocde AS c', 'c.id', '=', 'a.ocde_id')
      ->select(
        'a.titulo',
        'a.codigo_proyecto',
        'a.tipo_proyecto',
        'a.estado',
        'a.resolucion_rectoral',
        'a.resolucion_fecha',
        'a.comentario',
        'a.observaciones_admin',
        'a.fecha_inicio',
        'a.fecha_fin',
        'a.palabras_clave',
        'b.nombre AS linea',
        'c.linea AS ocde',
        'a.localizacion'
      )
      ->where('a.id', '=', $proyecto_id)
      ->get();

    return ['data' => $detalle];
  }

  public function miembros($proyecto_id) {
    $miembros = DB::table('Proyecto_integrante AS a')
      ->join('Proyecto_integrante_tipo AS b', 'b.id', '=', 'a.proyecto_integrante_tipo_id')
      ->join('Usuario_investigador AS c', 'c.id', '=', 'a.investigador_id')
      ->select(
        'b.nombre AS tipo_integrante',
        DB::raw('CONCAT(c.apellido1, " ", c.apellido2, " ", c.nombres) AS nombre'),
        'c.tipo AS tipo_investigador'
      )
      ->where('a.proyecto_id', '=', $proyecto_id)
      ->orderBy('b.id')
      ->get();

    return ['data' => $miembros];
  }

  public function cartas($proyecto_id) {
    $s3 = $this->s3Client;

    $cartas = DB::table('Proyecto_doc')
      ->select(
        'id',
        'nombre',
        'archivo'
      )
      ->where('proyecto_id', '=', $proyecto_id)
      ->get();

    //  Obtener objetos del bucket
    foreach ($cartas as $carta) {
      $url = null;
      if ($carta->archivo != null) {
        $cmd = $s3->getCommand('GetObject', [
          'Bucket' => 'proyecto-docs',
          'Key' => $carta->archivo
        ]);
        //  Generar url temporal
        $url = (string) $s3->createPresignedRequest($cmd, '+5 minutes')->getUri();
      }
      $carta->url = $url;
    }

    return ['data' => $cartas];
  }

  public function descripcion($proyecto_id) {
    $descripcion = DB::table('Proyecto_descripcion')
      ->select(
        'id',
        'codigo',
        'detalle'
      )
      ->where('proyecto_id', '=', $proyecto_id)
      ->get();

    $detalles = [];
    foreach ($descripcion  as $data) {
      switch ($data->codigo) {
        case "resumen_ejecutivo":
          $detalles["resumen_ejecutivo"] = $data->detalle;
          break;
        case "antecedentes":
          $detalles["antecedentes"] = $data->detalle;
          break;
        case "objetivos":
          $detalles["objetivos"] = $data->detalle;
          break;
        case "justificacion":
          $detalles["justificacion"] = $data->detalle;
          break;
        case "hipotesis":
          $detalles["hipotesis"] = $data->detalle;
          break;
        case "metodologia_trabajo":
          $detalles["metodologia_trabajo"] = $data->detalle;
          break;
        case "referencias_bibliograficas":
          $detalles["referencias_bibliograficas"] = $data->detalle;
          break;
        case "contribucion_impacto":
          $detalles["contribucion_impacto"] = $data->detalle;
          break;
        default:
          break;
      }
    }

    return ['data' => $detalles];
  }

  public function actividades($proyecto_id) {
    $actividades = DB::table('Proyecto_actividad')
      ->select(
        'id',
        'actividad',
        'fecha_inicio',
        'fecha_fin'
      )
      ->where('proyecto_id', '=', $proyecto_id)
      ->get();

    return ['data' => $actividades];
  }

  public function presupuesto($proyecto_id) {
    $presupuesto = DB::table('Proyecto_presupuesto AS a')
      ->join('Partida AS b', 'b.id', '=', 'a.partida_id')
      ->select(
        'a.tipo',
        'b.partida',
        'a.justificacion',
        'a.monto',
      )
      ->where('a.proyecto_id', '=', $proyecto_id)
      ->orderBy('a.tipo')
      ->get();

    //  Info de presupuesto
    $info = [
      'bienes_monto' => 0.00, 'bienes_cantidad' => 0,
      'servicios_monto' => 0.00, 'servicios_cantidad' => 0,
      'otros_monto' => 0.00, 'otros_cantidad' => 0
    ];

    foreach ($presupuesto as $data) {
      if ($data->tipo == "Bienes") {
        $info["bienes_monto"] += $data->monto;
        $info["bienes_cantidad"]++;
      }
      if ($data->tipo == "Servicios") {
        $info["servicios_monto"] += $data->monto;
        $info["servicios_cantidad"]++;
      }
      if ($data->tipo == "Otros") {
        $info["otros_monto"] += $data->monto;
        $info["otros_cantidad"]++;
      }
    }

    $info["bienes_porcentaje"] = number_format(($info["bienes_monto"] / ($info["bienes_monto"] + $info["servicios_monto"] + $info["otros_monto"])) * 100, 2);
    $info["servicios_porcentaje"] = number_format(($info["servicios_monto"] / ($info["bienes_monto"] + $info["servicios_monto"] + $info["otros_monto"])) * 100, 2);
    $info["otros_porcentaje"] = number_format(($info["otros_monto"] / ($info["bienes_monto"] + $info["servicios_monto"] + $info["otros_monto"])) * 100, 2);

    return ['data' => $presupuesto, 'info' => $info];
  }

  public function responsable($proyecto_id) {
    $responsable = DB::table('Proyecto_integrante AS a')
      ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
      ->leftJoin('Docente_categoria AS c', 'c.categoria_id', '=', 'b.docente_categoria')
      ->leftJoin('Facultad AS d', 'd.id', '=', 'b.facultad_id')
      ->leftJoin('Dependencia AS e', 'e.id', '=', 'b.dependencia_id')
      ->leftJoin('Grupo AS f', 'f.id', '=', 'a.grupo_id')
      ->select(
        # Datos personales
        DB::raw('CONCAT(b.apellido1, " " , b.apellido2) AS apellidos'),
        'b.nombres',
        'b.doc_numero',
        'b.telefono_movil',
        'b.telefono_trabajo',
        # Datos profesionales
        'b.especialidad',
        'b.titulo_profesional',
        'b.grado',
        'b.tipo',
        DB::raw('CONCAT(c.categoria, " | ", c.clase) AS docente_categoria'),
        # Datos institucionales
        'b.codigo',
        'd.nombre AS facultad',
        'e.dependencia',
        'b.email3',
        # Datos grupo
        'f.grupo_nombre',
        'f.grupo_nombre_corto'
      )
      ->where('a.condicion', '=', 'Responsable')
      ->where('a.proyecto_id', '=', $proyecto_id)
      ->get();

    return ['data' => $responsable];
  }
}
