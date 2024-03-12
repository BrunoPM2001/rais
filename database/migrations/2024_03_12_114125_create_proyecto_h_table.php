<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_H', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('linea_id')->nullable();
      $table->unsignedBigInteger('instituto_id')->nullable();
      $table->unsignedBigInteger('facultad_id')->nullable();
      $table->unsignedBigInteger('mlinea_id')->nullable();
      $table->string('codigo', 10)->nullable();
      $table->string('codigo_concytec', 10);
      $table->string('codigo_unesco', 10);
      $table->string('tipo', 15);
      $table->string('titulo', 600);
      $table->text('resumen')->nullable();
      $table->text('antecedentes')->nullable();
      $table->text('justificacion')->nullable();
      $table->text('hipotesis')->nullable();
      $table->text('objetivos')->nullable();
      $table->text('metas')->nullable();
      $table->text('contribucion')->nullable();
      $table->text('metodologia')->nullable();
      $table->mediumText('bibliografia')->nullable();
      $table->text('des10')->nullable();
      $table->text('des11')->nullable();
      $table->text('des12')->nullable();
      $table->string('localizacion', 120)->nullable();
      $table->string('resolucion_decanal', 30)->nullable();
      $table->string('resolucion', 30)->nullable();
      $table->string('resolucion_oficina', 50)->nullable();
      $table->date('resolucion_fecha')->nullable();
      $table->string('tipo_investigacion', 30)->nullable();
      $table->string('clase_investigacion', 15)->nullable();
      $table->dateTime('fecha_inscripcion')->nullable();
      $table->integer('periodo')->nullable();
      $table->integer('tiempo')->nullable();
      $table->decimal('monto', 10)->nullable();
      $table->text('comentario')->nullable();
      $table->date('fecha_inicio')->nullable();
      $table->date('fecha_fin')->nullable();
      $table->integer('excluido')->nullable();
      $table->date('excluido_fecha')->nullable();
      $table->integer('carta_compromiso')->nullable();
      $table->date('carta_compromiso_fecha')->nullable();
      $table->string('carta_compromiso_docref', 50)->nullable();
      $table->string('tesis_tipo', 100)->nullable();
      $table->string('tesis_nivel', 50)->nullable();
      $table->string('tesis_financiamiento', 50)->nullable();
      $table->string('tesis_tipo_proyecto', 50)->nullable();
      $table->string('tesis_estado', 50)->nullable();
      $table->string('tesis_programa', 50)->nullable();
      $table->decimal('tesis_monto_facultad', 10)->nullable();
      $table->decimal('tesis_monto_otro', 10)->nullable();
      $table->char('tesis_opcion', 1)->nullable();
      $table->string('grupo_nombre', 150)->nullable();
      $table->string('grupo_clasificacion', 50)->nullable();
      $table->string('grupo_estado', 15)->nullable();
      $table->tinyInteger('aprobado')->nullable();
      $table->tinyInteger('step')->nullable();
      $table->integer('status')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_H');
  }
};
