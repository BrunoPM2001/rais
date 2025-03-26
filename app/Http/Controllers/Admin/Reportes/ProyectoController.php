<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ProyectoController extends Controller {

  public function reporte($facultad, $tipo, $periodo) {

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
        'p.condicion_gi',
        'p.grupo_id',
        'p.total_presupuesto as presupuesto'
      )
      ->where('p.tipo_proyecto', '=', $tipo)
      ->where('p.facultad_id_proyecto', '=', $facultad)
      ->where('p.periodo', '=', $periodo)
      ->orderByRaw('p.grupo_id, p.proyecto_id, p.proyecto_integrante_tipo_id')
      ->get();

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
      default:
        $tipo = 'Tipo de Proyecto Desconocido';
    }

    $pdf = Pdf::loadView('admin.reportes.proyectoPDF', ['lista' => $proyectos, 'periodo' => $periodo, 'tipo' => $tipo]);
    return $pdf->stream();
  }
}
