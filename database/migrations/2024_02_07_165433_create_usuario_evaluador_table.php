<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Usuario_evaluador', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('usuario_investigador_id')->nullable();
      $table->string('tipo');
      $table->string('apellidos');
      $table->string('nombres');
      $table->string('institucion')->nullable();
      $table->string('cargo')->nullable();
      $table->string('codigo_regina')->nullable();
      $table->string('renacyt', 50)->nullable();
      $table->string('nivel_renacyt', 5)->nullable();
      $table->string('cti_vitae', 100)->nullable();
      $table->string('codigo_orcid', 100)->nullable();
      $table->string('numero_dni', 100)->nullable();

      //  Fks
      $table->foreign('usuario_investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Usuario_evaluador');
  }
};
