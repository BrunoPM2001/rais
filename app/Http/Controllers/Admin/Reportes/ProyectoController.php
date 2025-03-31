<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProyectoController extends Controller {

  public function reporte(Request $request) {

    $adminId = $request->attributes->get('token_decoded')->id;

    $admin = DB::table('Usuario_admin AS admin')
      ->join('Usuario AS ux', 'admin.id', '=', 'ux.tabla_id')
      ->select('ux.username as nombres')
      ->where('admin.id', '=', $adminId)
      ->first();

    $facultad = $request->query('facultad');
    $periodo = $request->query('periodo');
    $tipo = $request->query('tipo_proyecto');


    $proyectos = DB::table('view_proyecto_gi as p')
      ->select(
        'p.sigla_area AS sigla',
        'p.area as area',
        'p.grupo_nombre_corto',
        'p.grupo_nombre',
        'p.facultad_proyecto as facultad_grupo',
        'p.codigo_proyecto',
        'p.titulo',
        'p.integrante as nombres',
        'p.condicion_proyecto as condicion',
        'p.facultad_docente as facultad_miembro',
        'p.codigo_docente as codigo',
        DB::raw("CASE 
        WHEN p.tipo_investigador = 'DOCENTE PERMANENTE' THEN 'Docente permanente'
        WHEN p.tipo_investigador = 'Estudiante Pre Grado' THEN 'Estudiante pregrado'
        ELSE p.tipo_investigador
    END AS tipo_investigador"),
        'p.condicion_gi',
        'p.grupo_id',
        'p.total_presupuesto as presupuesto'
      )
      ->where('p.tipo_proyecto', '=', $tipo)
      ->where('p.facultad_id_proyecto', '=', $facultad)
      ->where('p.periodo', '=', $periodo)
      ->orderByRaw('p.grupo_nombre, 
      p.codigo_proyecto, 
      FIELD(p.proyecto_integrante_tipo_id,
        1, 2, 3, 5, 6, 4, 31, 32, 33, 35,
        7, 8, 9, 11, 12, 10, 42, 50, 51, 52, 55,
        13, 14, 53, 54,
        15, 16,
        17, 18,
        19, 20,
        21, 22, 23, 24, 26, 25, 27,
        28, 29,
        30, 34,
        36, 37, 38, 40, 41, 39,
        44, 45, 46, 47, 48, 49, 90, 91,
        56, 57, 58, 59, 60, 61, 94, 62, 63, 64, 65, 82,
        66, 67, 68, 69,
        70, 71,
        74, 75, 76, 77, 78, 79, 80, 81,
        83, 84, 85,
        86, 88,
        92, 93
      ),
      p.integrante')
      ->get();

    $area = DB::table('Facultad AS fx')
      ->leftJoin('Area as ax', 'ax.id', '=', 'fx.area_id')
      ->select('ax.nombre', 'ax.sigla', 'fx.nombre as facultad')
      ->where('fx.id', '=', $facultad)
      ->first();

    $qrUrl = "https://vrip.unmsm.edu.pe/convocatorias/"; // Aquí va la URL fija del sistema RAIS
    $qrCode = base64_encode(QrCode::format('png')->size(200)->generate($qrUrl));


    switch ($tipo) {
      case 'PCONFIGI':
        $tipo = 'Proyectos de Investigación con Financiamiento para Grupos de Investigación';
        break;
      case 'PCONFIGI-INV':
        $tipo = 'Proyectos de Innovación para  Grupos de Investigación “INNOVA SAN MARCOS';
        break;
      case 'PRO-CTIE':
        $tipo = 'Proyectos de Ciencia, Tecnología, Innovación y Emprendimiento (PRO-CTIE) para Estudiantes de la UNMSM';
        break;
      case 'ECI':
        $tipo = 'Programa de Equipamiento Científico para la Investigación de la UNMSM';
        break;
      case 'PSINFIPU':
        $tipo = 'Proyectos de Publicación Académica para Grupos de Investigación';
        break;
      default:
        $tipo = 'Tipo de Proyecto Desconocido';
    }

    $pdf = Pdf::loadView('admin.reportes.proyectoPDF', [
      'lista' => $proyectos,
      'periodo' => $periodo,
      'tipo' => $tipo,
      'area' => $area,
      'admin' => $admin,
      'qr' => $qrCode,
    ]);
    return $pdf->stream();
  }
}
