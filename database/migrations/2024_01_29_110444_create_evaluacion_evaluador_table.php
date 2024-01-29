<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Evaluacion_evaluador', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('evaluacion_facultad_id');
      $table->unsignedBigInteger('evaluador_usuario_id');
      //  Fks
      $table->foreign('evaluacion_facultad_id')->references('id')->on('Evaluacion_facultad')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
      $table->foreign('evaluador_usuario_id')->references('id')->on('Evaluador_usuario')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Evaluacion_evaluador');
  }
};
