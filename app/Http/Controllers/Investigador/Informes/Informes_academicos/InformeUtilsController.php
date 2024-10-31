<?php

namespace App\Http\Controllers\Investigador\Informes\Informes_academicos;

use App\Http\Controllers\S3Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformeUtilsController extends S3Controller {

  public function getData(Request $request) {
    switch ($request->query('tipo_proyecto')) {
      case "ECI":
        $util = new InformeEciController();
        return $util->getData($request);
      case "PCONFIGI":
        $util = new InformePconfigiController();
        return $util->getData($request);
      case "PCONFIGI-INV":
        $util = new InformePconfigiInvController();
        return $util->getData($request);
      case "PINTERDIS":
        $util = new InformePinterdisController();
        return $util->getData($request);
      case "PINVPOS":
        $util = new InformePinvposController();
        return $util->getData($request);
      case "PMULTI":
        $util = new InformePmultiController();
        return $util->getData($request);
      case "PSINFINV":
        $util = new InformePsinfinvController();
        return $util->getData($request);
      case "PSINFIPU":
        $util = new InformePsinfipuController();
        return $util->getData($request);
      case "PTPBACHILLER":
        $util = new InformePtpbachillerController();
        return $util->getData($request);
      case "PTPDOCTO":
        $util = new InformePtpdoctoController();
        return $util->getData($request);
      case "PRO-CTIE":
        $util = new InformeProCtieController();
        return $util->getData($request);
    }
  }

  public function sendData(Request $request) {
    switch ($request->input('tipo_proyecto')) {
      case "ECI":
        $util = new InformeEciController();
        return $util->sendData($request);
      case "PCONFIGI":
        $util = new InformePconfigiController();
        return $util->sendData($request);
      case "PCONFIGI-INV":
        $util = new InformePconfigiInvController();
        return $util->sendData($request);
      case "PINTERDIS":
        $util = new InformePinterdisController();
        return $util->sendData($request);
      case "PINVPOS":
        $util = new InformePinvposController();
        return $util->sendData($request);
      case "PMULTI":
        $util = new InformePmultiController();
        return $util->sendData($request);
      case "PSINFINV":
        $util = new InformePsinfinvController();
        return $util->sendData($request);
      case "PSINFIPU":
        $util = new InformePsinfipuController();
        return $util->sendData($request);
      case "PTPBACHILLER":
        $util = new InformePtpbachillerController();
        return $util->sendData($request);
      case "PTPDOCTO":
        $util = new InformePtpdoctoController();
        return $util->sendData($request);
      case "PRO-CTIE":
        $util = new InformeProCtieController();
        return $util->sendData($request);
    }
  }

  public function presentar(Request $request) {
    switch ($request->input('tipo_proyecto')) {
      case "ECI":
        $util = new InformeEciController();
        return $util->presentar($request);
      case "PCONFIGI":
        $util = new InformePconfigiController();
        return $util->presentar($request);
      case "PCONFIGI-INV":
        $util = new InformePconfigiInvController();
        return $util->presentar($request);
      case "PINTERDIS":
        $util = new InformePinterdisController();
        return $util->presentar($request);
      case "PINVPOS":
        $util = new InformePinvposController();
        return $util->presentar($request);
      case "PMULTI":
        $util = new InformePmultiController();
        return $util->presentar($request);
      case "PSINFINV":
        $util = new InformePsinfinvController();
        return $util->presentar($request);
      case "PSINFIPU":
        $util = new InformePsinfipuController();
        return $util->presentar($request);
      case "PTPBACHILLER":
        $util = new InformePtpbachillerController();
        return $util->presentar($request);
      case "PTPDOCTO":
        $util = new InformePtpdoctoController();
        return $util->presentar($request);
      case "PRO-CTIE":
        $util = new InformeProCtieController();
        return $util->presentar($request);
    }
  }

  public function loadActividad(Request $request) {
    if ($request->hasFile('file')) {

      $date = Carbon::now();
      $date1 = Carbon::now();

      $name = $date1->format('Ymd-His');
      $nameFile = $request->input('proyecto_id') . "/" . $name . "." . $request->file('file')->getClientOriginalExtension();
      $this->uploadFile($request->file('file'), "proyecto-doc", $nameFile);

      DB::table('Proyecto_doc')
        ->updateOrInsert([
          'proyecto_id' => $request->input('proyecto_id'),
          'nombre' => 'Actividades',
          'categoria' => 'actividad' . $request->input('indice'),
        ], [
          'comentario' => $date,
          'archivo' => $nameFile,
          'estado' => 1
        ]);

      return ['message' => 'success', 'detail' => 'Archivo cargado correctamente'];
    } else {
      return ['message' => 'error', 'detail' => 'Error al cargar archivo'];
    }
  }

  public function reporte(Request $request) {
    switch ($request->query('tipo_proyecto')) {
      case "PCONFIGI":
        $util = new InformePconfigiController();
        return $util->reporte($request);
        break;
      case "PCONFIGI-INV":
        $util = new InformePconfigiInvController();
        return $util->reporte($request);
        break;
      case "PSINFINV":
        $util = new InformePsinfinvController();
        return $util->reporte($request);
        break;
      case "PSINFIPU":
        $util = new InformePsinfipuController();
        return $util->reporte($request);
        break;
      case "PRO-CTIE":
        $util = new InformeProCtieController();
        return $util->reporte($request);
    }
  }
}
