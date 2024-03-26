<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_actividad', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_id');
      $table->unsignedBigInteger('proyecto_integrante_id')->nullable();
      $table->string('actividad');
      $table->date('fecha_inicio');
      $table->date('fecha_fin');
      $table->text('justificacion')->nullable();

      //  Fks
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
      $table->foreign('proyecto_integrante_id')->references('id')->on('Proyecto_integrante');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_actividad');
  }
};
