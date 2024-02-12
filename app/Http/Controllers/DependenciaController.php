<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dependencia;
use App\Models\Facultad;
use Illuminate\Support\Facades\DB;

class DependenciaController extends Controller {

  public function getAll() {
    $dependencias = DB::table('Dependencia AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.dependencia',
        'b.nombre AS facultad'
      )
      ->get();

    return ['data' => $dependencias];
  }

  public function getOne($id) {
    $dependencia = DB::table('Dependencia AS a')
      ->leftJoin('Facultad AS b', 'b.id', '=', 'a.facultad_id')
      ->select(
        'a.id',
        'a.dependencia',
        'b.id AS facultad_id',
        'b.nombre AS facultad',
      )
      ->where('a.id', '=', $id)
      ->first();

    return $dependencia;
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

  public function update(Request $request) {
    $id = $request->input('id');
    //  Validar la data
    $request->validate([
      'facultad_id' => 'required|exists:Facultad,id',
      'dependencia' => 'required|string|unique:Dependencia,dependencia,' . $id . ',id|max:255',
    ]);

    //  Encontrar y actualizar data
    $dependencia = Dependencia::findOrFail($id);
    $dependencia->update($request->all());
    return [
      'result' => 'Success',
      'data' => $dependencia
    ];
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

    return view('admin.admin.dependencias', [
      'facultades' => $facultades
    ]);
  }
}
