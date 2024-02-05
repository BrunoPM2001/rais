<?php

use App\Http\Controllers\DependenciaController;
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
Route::get('/lineas', [Linea_investigacionController::class, 'main'])->name('view_lineas');
Route::get('/dependencias', [DependenciaController::class, 'main'])->name('view_dependencias');

//  Líneas de investigación
Route::get('/ajaxGetLineasInvestigacion', [Linea_investigacionController::class, 'getAll']);
Route::get('/ajaxGetLineasInvestigacionFacultad/{id}', [Linea_investigacionController::class, 'getAllOfFacultad']);

Route::post('/createLinea', [Linea_investigacionController::class, 'create'])->name('create_linea');


//  Dependencias
Route::get('/ajaxGetDependencias', [DependenciaController::class, 'getAll']);
Route::get('/ajaxGetDependencia/{id}', [DependenciaController::class, 'getOne']);

Route::post('/createDependencia', [DependenciaController::class, 'create'])->name('create_dependencia');
Route::post('/editDependencia', [DependenciaController::class, 'edit'])->name('edit_dependencia');
