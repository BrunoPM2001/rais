<?php

use App\Http\Controllers\Linea_investigacionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
  return view('welcome');
});

//  ADMIN VIEWS
Route::get('/lineas', [Linea_investigacionController::class, 'main']);

//  AJAX
Route::get('/ajaxGetLineasInvestigacionFacultad/{id}', [Linea_investigacionController::class, 'getAllOfFacultad']);
