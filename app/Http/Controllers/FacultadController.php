<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facultad;

class FacultadController extends Controller {

  public function getAll() {
    $facultades = Facultad::all();
    return $facultades;
  }

  public function create(Request $request) {
    //  Validar data
    $request->validate([
      'area_id' => 'required|exists:Area,id',
      'nombre' => 'required|string|unique:Facultad,nombre|max:255'
    ]);

    //  Insertar en la DB
    $facultad =  Facultad::create([
      'area_id' => $request->area_id,
      'nombre' => $request->nombre
    ]);
    return $facultad;
  }

  public function update(Request $request, $id) {
    //  Validar data
    $request->validate([
      'area_id' => 'required|exists:Area,id',
      'nombre' => 'required|string|unique:Facultad,nombre,' . $id . ',id|max:255'
    ]);

    //  Encontrar y actualizar data
    $facultad = Facultad::findOrFail($id);
    $facultad->update($request->all());

    return $facultad;
  }

  public function delete($id) {
    $facultad = Facultad::findOrFail($id);
    $facultad->delete();

    return $facultad;
  }
}
