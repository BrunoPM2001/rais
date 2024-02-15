<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Grupo_integrante', function (Blueprint $table) {
      $table->id();
      //  Null por 1 único caso
      $table->unsignedBigInteger('grupo_id')->nullable();
      $table->unsignedBigInteger('instituto_id')->nullable();
      $table->unsignedBigInteger('dependencia_id')->nullable();
      $table->unsignedBigInteger('facultad_id')->nullable();
      $table->unsignedBigInteger('investigador_id');
      $table->string('codigo', 8)->nullable();
      $table->string('tipo', 150);
      $table->string('permanencia', 50)->nullable();
      $table->string('especialidad', 100)->nullable();
      $table->string('dep_academico', 100)->nullable();
      $table->string('titulo_profesional', 100)->nullable();
      $table->string('grado', 50)->nullable();
      $table->string('docente_categoria', 10)->nullable();
      $table->string('condicion', 40)->nullable();
      $table->string('cargo', 20)->nullable();
      $table->decimal('puntaje')->nullable();
      $table->boolean('tesista')->nullable();
      $table->string('titulo_proyecto_tesis', 150)->nullable();
      $table->date('fecha_inclusion')->nullable();
      $table->date('fecha_exclusion')->nullable();
      $table->string('resolucion_decanal', 30)->nullable();
      $table->string('resolucion_oficina', 30)->nullable();
      $table->string('resolucion', 30)->nullable();
      $table->date('resolucion_fecha')->nullable();
      $table->text('observacion')->nullable();
      $table->text('resolucion_exclusion')->nullable();
      $table->date('resolucion_exclusion_fecha')->nullable();
      $table->text('resolucion_oficina_exclusion')->nullable();
      $table->text('observacion_excluir')->nullable();
      $table->integer('estado')->nullable();
      $table->integer('cod_orcid')->nullable();
      $table->integer('cod_dina')->nullable();
      $table->string('institucion')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('grupo_id')->references('id')->on('Grupo');
      $table->foreign('instituto_id')->references('id')->on('Instituto');
      $table->foreign('dependencia_id')->references('id')->on('Dependencia');
      $table->foreign('facultad_id')->references('id')->on('Facultad');
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Grupo_integrante');
  }
};
