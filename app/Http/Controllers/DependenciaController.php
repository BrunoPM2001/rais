<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dependencia;

class DependenciaController extends Controller {

  public function getAll() {
    $dependencias = Dependencia::all();
    return $dependencias;
  }

  public function create(Request $request) {
    //  Validar data
    $request->validate([
      'facultad_id' => 'required|exists:Facultad,id',
      'dependencia' => 'required|string|unique:Dependencia,dependencia|max:255'
    ]);

    //  Insertar en la DB
    $dependencia = Dependencia::create([
      'facultad_id' => $request->facultad_id,
      'dependencia' => $request->dependencia
    ]);
    return $dependencia;
  }

  public function update(Request $request, $id) {
    //  Validar la data
    $request->validate([
      'facultad_id' => 'required|exists:Facultad,id',
      'dependencia' => 'required|string|unique:Dependencia,dependencia,' . $id . ',id|max:255',
    ]);

    //  Encontrar y actualizar data
    $dependencia = Dependencia::findOrFail($id);
    $dependencia->update($request->all());
    return $dependencia;
  }

  public function delete($id) {
    $dependencia = Dependencia::findOrFail($id);
    $dependencia->delete();
    return $dependencia;
  }
}
