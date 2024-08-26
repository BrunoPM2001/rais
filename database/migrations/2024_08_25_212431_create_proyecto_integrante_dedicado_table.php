<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_integrante_dedicado', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('investigador_id');
      $table->unsignedBigInteger('proyecto_id')->nullable();
      $table->unsignedBigInteger('facultad_id');
      $table->string('apellido1');
      $table->string('apellido2');
      $table->string('nombre');
      $table->string('dni', 8);
      $table->string('email');
      $table->string('cargo');
      $table->string('condicion');
      $table->tinyInteger('encargado')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
      $table->foreign('facultad_id')->references('id')->on('Facultad');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_integrante_dedicado');
  }
};
