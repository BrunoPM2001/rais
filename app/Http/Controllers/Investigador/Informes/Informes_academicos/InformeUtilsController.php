<?php

namespace App\Http\Controllers\Investigador\Informes\Informes_academicos;

use App\Http\Controllers\S3Controller;
use Illuminate\Http\Request;

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
      case "PMULTI":
        $util = new InformePmultiController();
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
    }
  }

  public function presentar(Request $request) {
    switch ($request->input('tipo_proyecto')) {
      case "ECI":
        $util = new InformeEciController();
        return $util->presentar($request);
    }
  }
}
