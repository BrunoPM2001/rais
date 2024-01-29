<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model {

  protected $table = 'Dependencia';
  protected $fillable = ['facultad_id', 'dependencia'];
  public $timestamps = false;
}
