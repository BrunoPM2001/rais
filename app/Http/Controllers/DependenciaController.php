<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dependencia;
use App\Models\Facultad;

class DependenciaController extends Controller {

  public function getAll() {
    $dependencias = Dependencia::with([
      'facultad' => function ($query) {
        $query->select('id', 'nombre');
      }
    ])->get();
    return ['data' => $dependencias];
  }

  public function getOne($id) {
    $dependencia = Dependencia::with([
      'facultad' => function ($query) {
        $query->select('id', 'nombre');
      }
    ])->where('id', '=', $id)->get();
    return $dependencia[0];
  }

  public function create(Request $request) {
    //  Validar data
    $request->validate([
      'facultad_id' => 'required|exists:Facultad,id',
      'dependencia' => 'required|string|unique:Dependencia,dependencia|max:255'
    ]);

    //  Insertar en la DB
    Dependencia::create([
      'facultad_id' => $request->facultad_id,
      'dependencia' => $request->dependencia
    ]);
    return redirect()->route('view_dependencias');
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

  //  Views
  public function main() {
    //  Lista de facultades
    $facultad = new Facultad();
    $facultades = $facultad->listar();

    return view('admin.dependencias', [
      'facultades' => $facultades
    ]);
  }
}
