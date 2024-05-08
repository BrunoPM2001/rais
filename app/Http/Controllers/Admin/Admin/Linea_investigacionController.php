<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Linea_investigacion;
use Illuminate\Support\Facades\Validator;

class Linea_investigacionController extends Controller {

  public function getAll($facultad_id) {
    $lineas_investigacion = Linea_investigacion::select('id', 'codigo', 'nombre')
      ->where('facultad_id', $facultad_id)
      ->get();

    return ['data' => $lineas_investigacion];
  }

  public function getAllOfFacultad($facultad_id) {
    $query = Linea_investigacion::with('hijos')
      ->whereNull('parent_id');

    if ($facultad_id == 'null') {
      $query->whereNull('facultad_id');
    } else {
      $query->where('facultad_id', $facultad_id);
    }
    $lineas_investigacion = $query->get();

    return ['data' => $lineas_investigacion];
  }

  public function create(Request $request) {
    //  Validar la data
    $validator =  Validator::make($request->all(), [
      'facultad_id' => 'nullable|exists:Facultad,id',
      'parent_id' => 'nullable|exists:Linea_investigacion,id',
      'codigo' => 'required|string|unique:Linea_investigacion,codigo|max:255',
      'nombre' => 'required|string|unique:Linea_investigacion,nombre|max:255',
      'resolucion' => 'nullable|string|max:255'
    ]);

    if ($validator->fails()) {
      return ['message' => 'error', 'detail' => 'Error al crear nueva línea de investigación'];
    }

    //  Insertar en la DB
    Linea_investigacion::create([
      'facultad_id' => $request->facultad_id,
      'parent_id' => $request->parent_id,
      'codigo' => $request->codigo,
      'nombre' => $request->nombre,
      'resolucion' => $request->resolucion
    ]);

    return ['message' => 'success', 'detail' => 'Línea de investigación creada con éxito'];
  }
}
