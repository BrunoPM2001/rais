<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investigador;

class InvestigadorController extends Controller {

  public function getAll() {
    $investigadores = Investigador::all();
    return $investigadores;
  }
}
