<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model {

  protected $table = 'Area';
  protected $fillable = ['sigla', 'nombre'];
}
