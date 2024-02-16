<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_evaluacion', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_id');
      $table->unsignedBigInteger('evaluador_id');
      $table->string('ficha')->nullable();
      $table->binary('comentario')->nullable();
      $table->text('resumen')->nullable();

      //  Fks
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
      $table->foreign('evaluador_id')->references('id')->on('Usuario_evaluador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_evaluacion');
  }
};
