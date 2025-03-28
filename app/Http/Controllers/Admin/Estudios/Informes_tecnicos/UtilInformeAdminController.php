<?php

namespace App\Http\Controllers\Admin\Estudios\Informes_tecnicos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtilInformeAdminController extends Controller {
  public function reporte(Request $request) {
    switch ($request->query('tipo_proyecto')) {
      case "PCONFIGI":
        $util = new PconfigiController();
        return $util->reporte($request);
        break;
      case "PRO-CTIE":
        $util = new ProctieController();
        return $util->reporte($request);
        break;
      case "PCONFIGI-INV":
        $util = new PconfigiInvController();
        return $util->reporte($request);
        break;
      case "PSINFINV":
        $util = new PsinfinvController();
        return $util->reporte($request);
        break;
      case "PSINFIPU":
        $util = new PsinfipuController();
        return $util->reporte($request);
        break;
      case "ECI":
        $util = new EciController();
        return $util->reporte($request);
        break;
      case "PTPGRADO":
        $util = new PtpgradoController();
        return $util->reporte($request);
        break;
      case "PTPBACHILLER":
        $util = new PtpbachillerController();
        return $util->reporte($request);
        break;
      case "PTPMAEST":
        $util = new PtpmaestController();
        return $util->reporte($request);
        break;
      case "PTPDOCTO":
        $util = new PtpdoctoController();
        return $util->reporte($request);
        break;
      case "PINTERDIS":
        $util = new PinterdisController();
        return $util->reporte($request);
        break;
    }
  }
}
