<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Usuario_investigador', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('dependencia_id')->nullable();
      $table->unsignedBigInteger('facultad_id')->nullable();
      $table->unsignedBigInteger('instituto_id')->nullable();
      $table->string('codigo')->nullable();
      $table->string('codigo_orcid')->nullable();
      $table->boolean('dina')->nullable();
      $table->string('apellido1')->nullable();
      $table->string('apellido2')->nullable();
      $table->string('nombres')->nullable();
      $table->string('doc_tipo')->nullable();
      $table->string('doc_numero')->nullable();
      $table->char('sexo', 1)->nullable();
      $table->date('fecha_nac')->nullable();
      $table->string('grado')->nullable();
      $table->string('especialidad')->nullable();
      $table->string('titulo_profesional')->nullable();
      $table->string('tipo')->nullable();
      $table->string('docente_categoria')->nullable();
      $table->string('direccion1')->nullable();
      $table->string('direccion2')->nullable();
      $table->date('fecha_icsi')->nullable();
      $table->string('email1')->nullable();
      $table->string('email2')->nullable();
      $table->string('email3')->nullable();
      $table->float('indice_h')->nullable();
      $table->string('indice_h_url')->nullable();
      $table->boolean('regina')->nullable();
      $table->string('researcher_id')->nullable();
      $table->string('scopus_id')->nullable();
      $table->string('google_scholar')->nullable();
      $table->string('palabras_clave')->nullable();
      $table->string('telefono_casa')->nullable();
      $table->string('telefono_trabajo')->nullable();
      $table->string('telefono_movil')->nullable();
      $table->string('teleahorro')->nullable();
      $table->string('facebook')->nullable();
      $table->string('twitter')->nullable();
      $table->string('link')->nullable();
      $table->string('pais')->nullable();
      $table->string('institucion')->nullable();
      $table->string('pais_institucion')->nullable();
      $table->string('posicion_unmsm')->nullable();
      $table->text('biografia')->nullable();
      $table->integer('estado')->nullable();
      $table->string('enlace_cti')->nullable();
      $table->string('tipo_investigador')->nullable();
      $table->string('tipo_investigador_categoria')->nullable();
      $table->string('tipo_investigador_programa')->nullable();
      $table->string('tipo_investigador_estado')->nullable();
      $table->string('renacyt')->nullable();
      $table->string('renacyt_nivel')->nullable();
      $table->string('cti_vitae')->nullable();
      $table->boolean('rrhh_status')->nullable();
      $table->string('dep_academico')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('dependencia_id')->references('id')->on('Dependencia');
      $table->foreign('facultad_id')->references('id')->on('Facultad');
      $table->foreign('instituto_id')->references('id')->on('Instituto');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Usuario_investigador');
  }
};
