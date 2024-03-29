<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Informe_tecnico', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_id');
      $table->unsignedBigInteger('informe_tipo_id')->nullable();
      $table->text('resumen_ejecutivo')->nullable();
      $table->string('palabras_clave')->nullable();
      $table->date('fecha_presentacion')->nullable();
      $table->string('registro_nro_vrip', 50)->nullable();
      $table->date('fecha_registro_csi')->nullable();
      $table->date('fecha_evento')->nullable();
      $table->date('fecha_informe_tecnico')->nullable();
      $table->mediumText('objetivos_taller')->nullable();
      $table->text('resultados_taller')->nullable();
      $table->text('propuestas_taller')->nullable();
      $table->text('conclusion_taller')->nullable();
      $table->text('recomendacion_taller')->nullable();
      $table->text('asistencia_taller')->nullable();
      $table->mediumText('infinal1')->nullable();
      $table->mediumText('infinal2')->nullable();
      $table->mediumText('infinal3')->nullable();
      $table->mediumText('infinal4')->nullable();
      $table->mediumText('infinal5')->nullable();
      $table->mediumText('infinal6')->nullable();
      $table->mediumText('infinal7')->nullable();
      $table->mediumText('infinal8')->nullable();
      $table->mediumText('infinal9')->nullable();
      $table->mediumText('infinal10')->nullable();
      $table->mediumText('infinal11')->nullable();
      $table->text('observaciones')->nullable();
      $table->text('observaciones_admin')->nullable();
      $table->tinyInteger('estado')->default(0);
      $table->date('fecha_envio')->nullable();
      $table->string('estado_trabajo')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Informe_tecnico');
  }
};
