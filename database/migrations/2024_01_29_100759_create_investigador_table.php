<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Investigador', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('dependencia_id')->nullable();
      $table->unsignedBigInteger('facultad_id')->nullable();
      $table->unsignedBigInteger('instituto_id')->nullable();
      $table->string('codigo');
      $table->string('codigo_orcid');
      $table->boolean('dina');
      $table->string('apellido1');
      $table->string('apellido2');
      $table->string('nombres');
      $table->string('doc_tipo');
      $table->string('doc_numero');
      $table->char('sexo', 1);
      $table->date('fecha_nac');
      $table->string('grado');
      $table->string('especialidad');
      $table->string('titulo_profesional');
      $table->string('tipo');
      $table->string('docente_categoria');
      $table->string('direccion1');
      $table->string('direccion2');
      $table->date('fecha_icsi');
      $table->string('email1');
      $table->string('email2');
      $table->string('email3');
      $table->float('indice_h');
      $table->string('indice_h_url');
      $table->boolean('regina');
      $table->string('researcher_id');
      $table->string('scopus_id');
      $table->string('google_scholar');
      $table->string('palabras_clave');
      $table->string('telefono_casa');
      $table->string('telefono_trabajo');
      $table->string('telefono_movil');
      $table->string('teleahorro');
      $table->string('facebook');
      $table->string('twitter');
      $table->string('link');
      $table->string('pais');
      $table->string('institucion');
      $table->string('pais_institucion');
      $table->string('posicion_unmsm');
      $table->text('biografia');
      $table->integer('estado');
      $table->string('tmp_facultad');
      $table->string('tmp_id');
      $table->string('enlace_cti');
      $table->string('tipo_investigador');
      $table->string('tipo_investigador_categoria');
      $table->string('tipo_investigador_programa');
      $table->string('tipo_investigador_estado');
      $table->string('renacyt');
      $table->string('renacyt_nivel');
      $table->string('cti_vitae');
      $table->boolean('rrhh_status');
      $table->string('dep_academico');
      //  Fks
      $table->foreign('dependencia_id')->references('id')->on('Dependencia')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
      $table->foreign('facultad_id')->references('id')->on('Facultad')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
      $table->foreign('instituto_id')->references('id')->on('Instituto')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::down('Investigador');
  }
};
