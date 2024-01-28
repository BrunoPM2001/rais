<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;

class AreaController extends Controller {

  public function create(Request $request) {
    //  Validar data
    $request->validate([
      'sigla' => 'required|alpha:ascii|unique:Area,sigla|max:1',
      'nombre' => 'required|string|unique:Area,nombre|max:255'
    ]);

    //  Insertar en la DB
    Area::create([
      'sigla' => $request->sigla,
      'nombre' => $request->nombre
    ]);
  }
}
