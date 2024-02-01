<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facultad;
use App\Models\Linea_investigacion;

class Linea_investigacionController extends Controller {

  //  Gets

  //  AJAX
  public function getAll() {
    $lineas_investigacion = Linea_investigacion::select('id', 'codigo', 'nombre')
      ->get();
    return ["data" => $lineas_investigacion];
  }

  public function getAllOfFacultadPaginate($id, $page) {
    $query = Linea_investigacion::with('hijos')
      ->whereNull('parent_id');
    if ($id == 'null') {
      $query->whereNull('facultad_id');
    } else {
      $query->where('facultad_id', $id);
    }
    $query->get();
    $lineas_investigacion = $query->paginate(4, ['*'], 'page', $page);
    return $lineas_investigacion;
  }

  public function getAllOfFacultad($id) {
    $query = Linea_investigacion::with('hijos')
      ->whereNull('parent_id');
    if ($id == 'null') {
      $query->whereNull('facultad_id');
    } else {
      $query->where('facultad_id', $id);
    }
    $lineas_investigacion = $query->get();
    return $lineas_investigacion;
  }

  public function create(Request $request) {
    //  Validar la data
    $request->validate([
      'facultad_id' => 'nullable|exists:Facultad,id',
      'parent_id' => 'nullable|exists:Linea_investigacion,id',
      'codigo' => 'required|string|unique:Linea_investigacion,codigo|max:255',
      'nombre' => 'required|string|unique:Linea_investigacion,nombre|max:255',
      'resolucion' => 'nullable|string|max:255'
    ]);

    //  Insertar en la DB
    Linea_investigacion::create([
      'facultad_id' => $request->facultad_id,
      'parent_id' => $request->parent_id,
      'codigo' => $request->codigo,
      'nombre' => $request->nombre,
      'resolucion' => $request->resolucion
    ]);
    return redirect()->route('view_lineas');
  }

  public function update(Request $request, $id) {
    //  Validar la data
    $request->validate([
      'facultad_id' => 'required|exists:Facultad,id',
      'parent_id' => 'nullable|exists:Linea_investigacion,id',
      'codigo' => 'required|string|unique:Linea_investigacion,codigo,' . $id . ',id|max:255',
      'nombre' => 'required|string|unique:Linea_investigacion,nombre,' . $id . ',id|max:255',
      'resolucion' => 'string|max:255',
      'estado' => 'required|boolean'
    ]);

    //  Encontrar y actualizar data
    $linea_investigacion = Linea_investigacion::findOrFail($id);
    $linea_investigacion->update($request->all());
    return $linea_investigacion;
  }

  public function delete($id) {
    $linea_investigacion = Linea_investigacion::findOrFail($id);
    $linea_investigacion->delete();
    return $linea_investigacion;
  }

  //  Views
  public function main() {
    //  Lista de facultades
    $facultad = new Facultad();
    $facultades = $facultad->listar();

    //  Lista de lineas
    $query = Linea_investigacion::with('hijos')
      ->whereNull('parent_id')
      ->whereNull('facultad_id');
    $query->get();
    $lineas_investigacion = $query->paginate(10, ['*'], 'page', 1);
    return view('admin.lineas_investigacion', [
      'facultades' => $facultades,
      'lineas' => $lineas_investigacion
    ]);
  }
}
