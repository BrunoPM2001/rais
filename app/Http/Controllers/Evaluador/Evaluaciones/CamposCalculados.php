<?php

namespace App\Http\Controllers\Evaluador\Evaluaciones;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

//Controlador principal 
class CamposCalculados extends Controller
{
    public function obtenerPuntajesEvaluacion(Request $request) {
        $proyectoId = $request->query('proyecto_id');

        // Obtener puntajes calculados
        $puntajeDocenteInvestigador = $this->calcularDocenteInvestigador($proyectoId);
        $puntajeExperienciaRAIS = $this->calcularExperienciaRAIS($proyectoId);
        $puntajeCategoriaGI = $this->calcularCategoriaGI($proyectoId);
        $puntajeDocentesRecienteIngreso = $this->calcularDocentesRecienteIngreso($proyectoId);
        $puntajeLocalizacion = $this->calcularLocalizacionProyecto($proyectoId);

        // Calcular el total (solo ejemplo)
        $puntajeTotal = $puntajeDocenteInvestigador + $puntajeExperienciaRAIS + $puntajeCategoriaGI + $puntajeDocentesRecienteIngreso + $puntajeLocalizacion;

        return response()->json([
            'puntaje_docente_investigador' => $puntajeDocenteInvestigador,
            'puntaje_experiencia_rais' => $puntajeExperienciaRAIS,
            'puntaje_categoria_gi' => $puntajeCategoriaGI,
            'puntaje_docentes_reciente_ingreso' => $puntajeDocentesRecienteIngreso,
            'puntaje_localizacion' => $puntajeLocalizacion,
            'puntaje_total' => $puntajeTotal
        ]);
    }


    //Calculo de las K.1 
    public function calcularDocenteInvestigador($proyectoId) {
        // Consulta a la base de datos para contar los docentes investigadores
        $docentes = DB::table('Proyecto_integrante')
            ->join('Usuario_investigador', 'Proyecto_integrante.investigador_id', '=', 'Usuario_investigador.id')
            ->where('Proyecto_integrante.proyecto_id', $proyectoId)
            ->whereIn('Usuario_investigador.tipo', ['Docente Investigador UNMSM', 'Docente Investigador RENACYT'])
            ->count();

        // Puntos por cada tipo de investigador
        $puntaje = $docentes * 3; // Ejemplo: 3 puntos por cada docente investigador

        return $puntaje;
    }

    //Calculo de las K.2
    public function calcularExperienciaRAIS($proyectoId) {
        $investigadores = DB::table('Proyecto_integrante')
            ->join('Usuario_investigador', 'Proyecto_integrante.investigador_id', '=', 'Usuario_investigador.id')
            ->where('Proyecto_integrante.proyecto_id', $proyectoId)
            ->get();

        $totalRAIS = 0;
        foreach ($investigadores as $investigador) {
            $totalRAIS += $investigador->rais_score; // Asumiendo que tienes un campo de puntuación RAIS
        }

        $puntajeFinal = count($investigadores) > 0 ? ($totalRAIS * 0.1) / count($investigadores) : 0;
        return $puntajeFinal;
    }

    //Calculo de la K.3
    public function calcularCategoriaGI($proyectoId) {
        $categoriaGI = DB::table('Grupo')
            ->where('id', $proyectoId)
            ->value('grupo_categoria'); // Suponiendo que 'grupo_categoria' es un campo en la tabla 'Grupo'

        $puntaje = 0;
        switch ($categoriaGI) {
            case 'A': $puntaje = 6; break;
            case 'B': $puntaje = 4; break;
            case 'C': $puntaje = 2; break;
            case 'D': $puntaje = 1; break;
        }

        return $puntaje;
    }

    //Calculo de la k.4
    public function calcularDocentesRecienteIngreso($proyectoId) {
        $docentesRecienteIngreso = DB::table('Proyecto_integrante')
            ->join('Usuario_investigador', 'Proyecto_integrante.investigador_id', '=', 'Usuario_investigador.id')
            ->where('Proyecto_integrante.proyecto_id', $proyectoId)
            ->where('Usuario_investigador.fecha_ingreso', '>=', '2023-01-01') // Suponiendo que tienes un campo 'fecha_ingreso'
            ->count();

        return $docentesRecienteIngreso;
    }

    //Calculo de la k.5
    public function calcularLocalizacionProyecto($proyectoId) {
        $localizacion = DB::table('Proyecto')
            ->where('id', $proyectoId)
            ->value('localizacion'); // Suponiendo que 'localizacion' es un campo en la tabla 'Proyecto'

        $puntaje = 0;
        switch ($localizacion) {
            case 'Lima': $puntaje = 0.5; break;
            case 'Lima Metropolitana': $puntaje = 1.0; break;
            case 'Callao': $puntaje = 2.0; break;
            case 'Otras Regiones': $puntaje = 3.0; break;
        }

        return $puntaje;
    }
}
