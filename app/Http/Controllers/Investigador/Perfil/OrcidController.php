<?php

namespace App\Http\Controllers\Investigador\Perfil;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OrcidController extends Controller {

  const URL_DEV = "https://sandbox.orcid.org/oauth/";
  const URL_PROD = "https://orcid.org/oauth/";
  const BASE_URL = self::URL_DEV;

  public function validarRegistro(Request $request) {
    $count = DB::table('Token_investigador_orcid')
      ->where('investigador_id', '=', $request->attributes->get('token_decoded')->investigador_id)
      ->count();

    if ($count == 0) {
      return [
        'message' => 'warning',
        'text' => 'No vinculado',
        'detail' => 'Vincular ORCID',
        'link' => $this->loginOrcidUrl()
      ];
    } else {
      return [
        'message' => 'success',
        'text' => 'Vinculado',
        'detail' => 'Su orcid ya está vinculado'
      ];
    }
  }

  public function obtenerTokens(Request $request) {
    $response = Http::asForm()->post(self::BASE_URL . 'token', [
      'client_id' => env('ORCID_CLIENT_ID'),
      'client_secret' => env('ORCID_CLIENT_SECRET'),
      'redirect_uri' => env('ORCID_REDIRECT_URI'),
      'grant_type' => 'authorization_code',
      'code' => $request->input('code'),
    ]);

    // Manejar la respuesta
    if ($response->successful()) {
      $data = $response->json();

      DB::table('Token_investigador_orcid')
        ->insert([
          'investigador_id' => $request->attributes->get('token_decoded')->investigador_id,
          'orcid' => $data['orcid'],
          'access_token' => $data['access_token'],
          'refresh_token' => $data['refresh_token'],
          'expires_in' => Carbon::now()->addSeconds($data['expires_in']),
          'scope' => $data['scope'],
        ]);

      return ['message' => 'success', 'detail' => 'Validación de ORCID exitosa'];
    } else {
      return [
        'message' => 'error',
        'detail' => 'Error obteniendo tokens',
      ];
    }
  }

  /*
  |-----------------------------------------------------------
  | Urls
  |-----------------------------------------------------------
  |
  | Funciones para generar las URLS para autenticación, 
  | obtención de tokens, listar publicaciones, etc.
  |
  */

  public function loginOrcidUrl() {
    $url = self::BASE_URL . "authorize?" . http_build_query([
      'client_id' => env('ORCID_CLIENT_ID'),
      'redirect_uri' => env('ORCID_REDIRECT_URI'),
      'response_type' => 'code',
      'scope' => '/authenticate /read-limited'
    ]);
    return $url;
  }
}
