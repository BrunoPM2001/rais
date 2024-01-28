<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;

class AreaController extends Controller {

  public function getAll() {
    $areas = Area::all();
    return $areas;
  }

  public function create(Request $request) {
    //  Validar data
    $request->validate([
      'sigla' => 'required|alpha:ascii|unique:Area,sigla|max:1',
      'nombre' => 'required|string|unique:Area,nombre|max:255'
    ]);

    //  Insertar en la DB
    $area = Area::create([
      'sigla' => $request->sigla,
      'nombre' => $request->nombre
    ]);
    return $area;
  }

  public function update(Request $request, $id) {
    //  Validar data
    $request->validate([
      'sigla' => 'required|alpha:ascci|unique:Area,sigla,' . $id . ',id|max:1',
      'nombre' => 'required|string|unique:Area,nombre,' . $id . ',id|max:255'
    ]);

    //  Encontrar y actualizar data
    $area = Area::findOrFail($id);
    $area->update($request->all());

    return $area;
  }

  public function delete($id) {
    $area = Area::findOrFail($id);
    $area->delete();

    return $area;
  }
}
