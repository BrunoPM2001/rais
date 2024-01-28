<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facultad extends Model {

  protected $table = 'Facultad';
  protected $fillable = ['area_id', 'nombre'];
}
