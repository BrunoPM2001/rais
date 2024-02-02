<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model {

  protected $table = 'Dependencia';
  protected $fillable = ['facultad_id', 'dependencia'];
  public $timestamps = false;

  public function facultad() {
    return $this->belongsTo(Facultad::class, 'facultad_id');
  }
}
