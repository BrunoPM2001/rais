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
      //  TODO - Completar campos
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
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Informe_tecnico');
  }
};
