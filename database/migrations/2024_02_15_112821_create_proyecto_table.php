<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('facultad_id')->nullable();
      $table->unsignedBigInteger('instituto_id')->nullable();
      $table->unsignedBigInteger('grupo_id')->nullable();
      $table->unsignedBigInteger('linea_id')->nullable();
      $table->unsignedBigInteger('ocde_id')->nullable();
      $table->string('codigo_proyecto', 20)->nullable();
      $table->boolean('innova_sm')->nullable();
      $table->string('titulo', 500)->nullable();
      $table->string('tipo_proyecto', 20)->nullable();
      $table->decimal('monto_asignado')->nullable();
      $table->boolean('dj_aceptada')->nullable();
      $table->date('fecha_inscripcion')->nullable();
      $table->date('fecha_inicio')->nullable();
      $table->date('fecha_fin')->nullable();
      $table->string('localizacion', 150)->nullable();
      $table->year('periodo')->nullable();
      $table->integer('convocatoria')->nullable();
      $table->integer('duracion_proyecto')->nullable();
      $table->string('palabras_clave', 250)->nullable();
      $table->integer('orden_merito')->nullable();
      $table->string('resolucion_rectoral', 20)->nullable();
      $table->date('resolucion_fecha')->nullable();
      $table->string('resolucion_decanal', 50)->nullable();
      $table->tinyInteger('deuda')->nullable();
      $table->text('comentario')->nullable();
      $table->text('observaciones_admin')->nullable();
      $table->tinyInteger('step')->default(1);
      $table->tinyInteger('estado')->default(1);
      $table->string('uuid', 50)->nullable();
      $table->integer('excluido')->default(0);
      $table->text('resolucion_anulacion')->nullable();
      $table->date('fecha_anulacion')->nullable();
      $table->boolean('autorizacion_grupo')->nullable();
      $table->tinyInteger('tipofin_id')->default(0);
      $table->decimal('aporte_unmsm')->nullable();
      $table->decimal('aporte_no_unmsm')->nullable();
      $table->decimal('financiamiento_fuente_externa')->nullable();
      $table->decimal('entidad_asociada')->nullable();
      $table->longText('carta_renuncia')->nullable();
      $table->integer('programa_id')->nullable();
      $table->tinyInteger('tesista_por_identificar')->nullable();
      $table->tinyInteger('condicion_aceptada')->nullable();
      $table->string('isbn')->nullable();
      $table->decimal('porcentaje_antiplagio')->nullable();
      $table->integer('eci_investigador_id')->nullable();
      $table->integer('eci_integrante_id')->nullable();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto');
  }
};
