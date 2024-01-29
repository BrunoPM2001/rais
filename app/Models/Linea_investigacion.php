<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Linea_investigacion extends Model {

  protected $table = 'Linea_investigacion';
  protected $fillable = ['facultad_id', 'parent_id', 'codigo', 'nombre', 'resolucion', 'estado'];

  //  Relaciones
  public function hijos(): HasMany {
    return $this->hasMany(Linea_investigacion::class, 'parent_id')->with('hijos');
  }
}
