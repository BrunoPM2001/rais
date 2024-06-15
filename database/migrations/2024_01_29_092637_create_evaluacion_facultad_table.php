<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Evaluacion_facultad', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('facultad_id');
      $table->unsignedInteger('cupos')->default(0);
      $table->unsignedFloat('puntaje_minimo')->nullable();
      $table->date('fecha_inicio');
      $table->date('fecha_fin');
      $table->date('evaluacion_fecha_inicio');
      $table->date('evaluacion_fecha_fin');
      $table->unsignedInteger('periodo');
      $table->string('tipo_proyecto', 15);
      $table->timestamps();
      //  Fks
      $table->foreign('facultad_id')->references('id')->on('Facultad');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Evaluacion_facultad');
  }
};
