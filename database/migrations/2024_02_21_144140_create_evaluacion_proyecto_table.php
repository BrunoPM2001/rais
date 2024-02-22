<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Evaluacion_proyecto', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('evaluacion_opcion_id')->nullable();
      $table->unsignedBigInteger('proyecto_id');
      $table->unsignedBigInteger('evaluador_id');
      $table->decimal('puntaje', 3, 1)->nullable();
      $table->boolean('cerrado')->default(false);
      $table->text('comentario')->nullable();
      $table->text('sustento_calificacion')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('evaluacion_opcion_id')->references('id')->on('Evaluacion_opcion');
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
      $table->foreign('evaluador_id')->references('id')->on('Usuario_evaluador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Evaluacion_proyecto');
  }
};
