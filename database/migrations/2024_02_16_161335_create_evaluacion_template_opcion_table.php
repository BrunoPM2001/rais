<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Evaluacion_template_opcion', function (Blueprint $table) {
      //  TODO - Eliminar las columnas de tipo y periodo (redundancia con la tabla con la que se tiene la relación)
      $table->id();
      $table->unsignedBigInteger('evaluacion_template_id');
      $table->text('opcion');
      $table->integer('puntaje_max');
      $table->integer('nivel');
      $table->integer('orden');
      $table->string('tipo', 50);
      $table->year('periodo');
      $table->integer('editable')->default(1);
      $table->string('otipo')->nullable();
      $table->integer('puntos_adicionales')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('evaluacion_template_id')->references('id')->on('Evaluacion_template');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Evaluacion_template_opcion');
  }
};
