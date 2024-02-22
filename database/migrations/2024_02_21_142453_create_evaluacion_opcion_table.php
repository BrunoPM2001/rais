<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Evaluacion_opcion', function (Blueprint $table) {
      $table->id();
      $table->text('opcion');
      $table->integer('puntaje_max');
      $table->integer('nivel');
      $table->integer('orden');
      $table->string('tipo', 50);
      $table->year('periodo');
      $table->integer('editable');
      $table->string('otipo')->nullable();
      $table->integer('puntos_adicionales')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Evaluacion_opcion');
  }
};
