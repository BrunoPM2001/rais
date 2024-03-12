<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class DeudoresController extends Controller {
  public function reporte($tipo, $año, $facultad, $fecha_impresion) {
    # TODO - Código para listar proyectos junto a sus integrantes 
  }
}
