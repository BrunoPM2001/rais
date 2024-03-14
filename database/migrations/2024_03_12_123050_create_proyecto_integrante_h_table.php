<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_integrante_H', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('instituto_id')->nullable();
      $table->unsignedBigInteger('dependencia_id')->nullable();
      $table->unsignedBigInteger('proyecto_id')->nullable();
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->unsignedBigInteger('facultad_id')->nullable();
      $table->string('codigo', 8)->nullable();
      $table->string('tipo', 50)->nullable();
      $table->string('especialidad', 100)->nullable();
      $table->string('dep_academico', 100)->nullable();
      $table->string('titulo_profesional', 100)->nullable();
      $table->string('grado', 50)->nullable();
      $table->string('docente_categoria', 10)->nullable();
      $table->string('condicion', 40)->nullable();
      $table->text('actividad', 40)->nullable();
      $table->decimal('puntaje', 10)->nullable();
      $table->dateTime('fecha_inclusion')->nullable();
      $table->date('fecha_exclusion')->nullable();
      $table->string('resolucion_decanal', 30)->nullable();
      $table->string('resolucion_oficina', 30)->nullable();
      $table->string('resolucion', 30)->nullable();
      $table->date('resolucion_fecha')->nullable();
      $table->text('observacion')->nullable();
      $table->integer('carta_compromiso')->nullable();
      $table->integer('status')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_integrante_H');
  }
};
