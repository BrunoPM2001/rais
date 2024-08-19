<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Informe_tecnico_H', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_id');
      $table->string('tipo');
      $table->string('tipo_informe');
      $table->text('resumen_ejecutivo')->nullable();
      $table->string('palabras_claves')->nullable();
      $table->text('actividades1')->nullable();
      $table->text('actividades2')->nullable();
      $table->text('resultado_preliminar')->nullable();
      $table->string('publicacion_lugar')->nullable();
      $table->date('publicacion_fecha')->nullable();
      $table->date('fecha_presentacion')->nullable();
      $table->string('registro_nro_vri', 50)->nullable();
      $table->date('registro_fecha_csi')->nullable();
      $table->date('fecha_evento')->nullable();
      $table->date('fecha_informe_tecnico')->nullable();
      $table->mediumText('objetivos_taller')->nullable();
      $table->text('resultados_taller')->nullable();
      $table->text('propuestas_taller')->nullable();
      $table->text('conclusion_taller')->nullable();
      $table->text('recomendacion_taller')->nullable();
      $table->text('asistencia_taller')->nullable();
      $table->text('infinal1')->nullable();
      $table->text('infinal2')->nullable();
      $table->text('infinal3')->nullable();
      $table->text('infinal4')->nullable();
      $table->text('infinal5')->nullable();
      $table->text('infinal6')->nullable();
      $table->text('infinal7')->nullable();
      $table->text('infinal8')->nullable();
      $table->text('infinal9')->nullable();
      $table->text('infinal10')->nullable();
      $table->text('infinal11')->nullable();
      $table->text('audit')->nullable();
      $table->integer('status');
      $table->timestamps();

      //  Fks
      $table->foreign('proyecto_id')->references('id')->on('Proyecto_H');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Informe_tecnico_H');
  }
};
