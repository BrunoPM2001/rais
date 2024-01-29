<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instituto;

class InstitutoController extends Controller {

  public function getAll() {
    $institutos = Instituto::all();
    return $institutos;
  }

  public function create(Request $request) {
    //  Validar data
    $request->validate([
      'facultad_id' => 'required|exists:Facultad,id',
      'instituto' => 'required|string|unique:Instituto,instituto|max:255'
    ]);

    //  Insertar en la DB
    $instituto = Instituto::create([
      'facultad_id' => $request->facultad_id,
      'instituto' => $request->instituto
    ]);
    return $instituto;
  }

  public function update(Request $request, $id) {
    //  Validar la data
    $request->validate([
      'facultad_id' => 'required|exists:Facultad,id',
      'instituto' => 'required|string|unique:Instituto,instituto,' . $id . ',id|max:255',
      'estado' => 'required|boolean',
    ]);

    //  Encontrar y actualizar data
    $instituto = Instituto::findOrFail($id);
    $instituto->update($request->all());
    return $instituto;
  }

  public function delete($id) {
    $instituto = Instituto::findOrFail($id);
    $instituto->delete();
    return $instituto;
  }
}
