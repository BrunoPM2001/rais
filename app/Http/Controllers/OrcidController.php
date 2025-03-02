<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrcidController extends Controller {

  const URL_DEV = "https://sandbox.orcid.org/oauth/";
  const URL_PROD = "https://orcid.org/oauth/";
  const BASE_URL = self::URL_PROD;

  const URL_API = "https://api.orcid.org/v3.0/";

  public function obtenerToken() {
    $response = Http::asForm()->post(self::BASE_URL . 'token', [
      'client_id' => env('ORCID_CLIENT_ID'),
      'client_secret' => env('ORCID_CLIENT_SECRET'),
      'grant_type' => 'client_credentials',
      'scope' => '/read-public',
    ]);

    // Manejar la respuesta
    if ($response->successful()) {
      $data = $response->json();

      return ['message' => 'success', 'detail' => 'Token para leer data obtenido', 'token' => $data['access_token']];
    } else {
      return [
        'message' => 'error',
        'detail' => 'Error obteniendo tokens',
      ];
    }
  }

  public function fetchData(Request $request) {
    $token = $this->obtenerToken();

    if ($token["message"] == "success") {
      $response = Http::withHeaders(
        [
          'Authorization' => 'Bearer ' . $token["token"],
          'Accept' => 'application/json'
        ]
      )->get(self::URL_API . $request->query('codigo_orcid'));

      if ($response->successful()) {
        $data = $response->json();

        $info = [
          'nombres' => $data["person"]["name"]["given-names"]["value"],
          'apellido1' => explode(' ', $data["person"]["name"]["family-name"]["value"])[0],
          'apellido2' => explode(' ', $data["person"]["name"]["family-name"]["value"])[1] ?? "",
          'pais' => $data["person"]["addresses"]["address"][0]["country"]["value"] ?? "",
          'email' => $data["person"]["emails"]["email"][0]["email"] ?? "",
          'institucion' => $data["activities-summary"]["employments"]["affiliation-group"][0]["summaries"][0]["employment-summary"]["organization"]["name"] ?? "",
          'titulo_profesional' => end($data["activities-summary"]["educations"]["affiliation-group"])["summaries"][0]["education-summary"]["role-title"] ?? ""
        ];

        return [
          'message' => 'success',
          'detail' =>  $info
        ];
      } else {
        return [
          'message' => 'error',
          'detail' => 'Error al ver perfil orcid',
        ];
      }
    } else {
      return [
        'message' => 'error',
        'detail' => 'Error obteniendo tokens1',
      ];
    }
  }
}
