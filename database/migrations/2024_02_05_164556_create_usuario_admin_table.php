<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Usuario_admin', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger("facultad_id");
      $table->string("codigo_trabajador")->default("");
      $table->string("apellido1");
      $table->string("apellido2");
      $table->string("nombres");
      $table->char("sexo", 1);
      $table->date("fecha_nacimiento")->nullable();
      $table->string("email")->nullable();
      $table->string("telefono_casa")->nullable();
      $table->string("telefono_trabajo")->nullable();
      $table->string("telefono_movil")->nullable();
      $table->string("direccion1")->nullable();
      $table->string("cargo")->nullable();
      $table->date("fecha_baja")->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Usuario_admin', function (Blueprint $table) {
      //
    });
  }
};
