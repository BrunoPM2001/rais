<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Model implements Authenticatable {

  protected $table = 'Usuario';
  public $timestamps = false;

  protected $fillable = [
    'username',
    'email',
    'password',
    'tabla',
    'tabla_id'
  ];

  //  Relaciones
  public function user_admin(): BelongsTo {
    return $this->belongsTo(Usuario_admin::class);
  }

  //  En caso de problemas aquí quitar la columna de email.
  public function getAuthIdentifierName() {
    return 'username' ?? 'email';
  }

  public function getAuthPassword() {
    return $this->password;
  }

  public function getAuthIdentifier() {
    return $this->getAttribute('username') ?? $this->getAttribute('email');
  }

  public function getRememberToken() {
    return $this->remember_token;
  }

  public function setRememberToken($value) {
    $this->remember_token = $value;
  }

  public function getRememberTokenName() {
    return 'remember_token';
  }
}
