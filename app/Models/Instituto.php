<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instituto extends Model {

  protected $table = 'Instituto';
  protected $fillable = ['facultad_id', 'instituto', 'estado'];
  public $timestamps = false;
}
