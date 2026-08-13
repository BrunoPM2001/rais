<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Exports\Admin\FromDataExport;
use Illuminate\Http\Request;
use App\Models\Linea_investigacion;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class Linea_investigacionController extends Controller {

  public function getAll($facultad_id) {
    $query = Linea_investigacion::select('id', 'codigo', 'nombre');

    if ($facultad_id == 'null') {
      $query->whereNull('facultad_id');
    } else {
      $query->where('facultad_id', $facultad_id);
    }
    $lineas_investigacion = $query->get();

    return ['data' => $lineas_investigacion];
  }

  public function getAllOfFacultad(Request $request, $facultad_id) {
    $estado = $request->query('estado');
    $query = Linea_investigacion::with('hijos')
      ->whereNull('parent_id');

    if ($facultad_id == 'null') {
      $query->whereNull('facultad_id');
    } else {
      $query->where('facultad_id', $facultad_id);
    }

    if (!is_null($estado)) {
      $query->where('estado', $estado);
    }

    $lineas_investigacion = $query->get();

    return ['data' => $lineas_investigacion];
  }

  public function gruposPorLinea($id){
    $grupos = DB::table('Grupo_linea as a')
        ->join('Grupo as b', 'b.id', '=', 'a.grupo_id')
        ->where('a.linea_investigacion_id', '=', $id)
        ->select('b.id', 'b.grupo_nombre as nombre')
        ->get();

    return ['data' => $grupos];
  }

  public function getOds() {
    $ods = DB::table('Ods')
      ->select('id', 'objetivo', 'descripcion')
      ->get();

    return ['data' => $ods];
  }

  public function create(Request $request) {
    //  Validar la data
    $validator =  Validator::make($request->all(), [
      'facultad_id' => 'nullable',
      'parent_id' => 'nullable|exists:Linea_investigacion,id',
      'codigo' => 'required|string|unique:Linea_investigacion,codigo|max:255',
      'nombre' => 'required|string|unique:Linea_investigacion,nombre|max:255',
      'resolucion' => 'nullable|string|max:255',
      'ods_principal' => 'nullable|exists:Ods,id',
      'ods_secundario' => 'nullable|array',
      'ods_secundario.*' => 'exists:Ods,id',
    ]);

    if ($validator->fails()) {
      return ['message' => 'error', 'detail' => 'Error al crear nueva línea de investigación'];
    }

    $facultad_id = ($request->facultad_id == 'null') ? null : $request->facultad_id;

    //  Insertar en la DB
    $linea =Linea_investigacion::create([
      'facultad_id' => $facultad_id,
      'parent_id' => $request->parent_id,
      'codigo' => $request->codigo,
      'nombre' => $request->nombre,
      'resolucion' => $request->resolucion
    ]);

    if ($request->filled('ods_principal')) {
      DB::table('Linea_investigacion_ods')->insert([
        'linea_investigacion_id' => $linea->id,
        'ods_id' => $request->ods_principal,
        'tipo' => 1
      ]);
    }

    if ($request->has('ods_secundario') && is_array($request->ods_secundario)) {
      foreach ($request->ods_secundario as $ods_id) {
        DB::table('Linea_investigacion_ods')->insert([
          'linea_investigacion_id' => $linea->id,
          'ods_id' => $ods_id,
          'tipo' => 2
        ]);
      }
    }

    return ['message' => 'success', 'detail' => 'Línea de investigación creada con éxito'];
  }

  public function update(Request $request) {

    $linea = Linea_investigacion::find($request->id);

    if (!$linea) {
      return ['message' => 'error', 'detail' => 'No se encontró la línea'];
    }

    $existeCodigo = Linea_investigacion::where('codigo', $request->codigo)
      ->where('id', '!=', $request->id)
      ->exists();

    if ($existeCodigo) {
      return ['message' => 'error', 'detail' => 'El código no puede repetirse'];
    }

    $linea->update([
      'codigo' => $request->codigo,
      'nombre' => $request->nombre,
      'estado' => $request->estado,
      'resolucion' => $request->resolucion,
      'parent_id' => $request->parent_id,
    ]);

    return ['message' => 'success', 'detail' => 'Actualizado correctamente'];
  }

  public function delete($id) {
    $existe = DB::table('Grupo_linea')
        ->where('linea_investigacion_id', $id)
        ->exists();

    if ($existe) {
        return ['message' => 'error', 'detail' => 'No se puede eliminar, existen grupos vinculados'];
    }

    Linea_investigacion::where('id', $id)->delete();

    return ['message' => 'success', 'detail' => 'Línea eliminada correctamente'];
  }

  public function excel(Request $request) {
    $data = $request->all();
    $export = new FromDataExport($data);

    return Excel::download($export, 'lineas_investigacion.xlsx');
  }

  public function pdf(Request $request) {
    $items = $request->items;
    $facultad = $request->facultad;
    $pdf = Pdf::loadView('admin.admin.lineasInvestigacionPDF', [
        'items' => $items,
        'facultad' => $facultad,
        'username' => $request->attributes->get('token_decoded')->username
    ]);
    return $pdf->download('lineas_investigacion.pdf');
  }
}
