<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Eval_docente_investigador', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->unsignedBigInteger('facultad_id');
      $table->string('tipo_investigador')->nullable();
      $table->string('tipo_eval');
      $table->dateTime('fecha_tramite')->nullable();
      $table->dateTime('fecha_constancia')->nullable();
      $table->dateTime('fecha_fin')->nullable();
      $table->string('nombres');
      $table->string('doc_numero');
      $table->char('sexo')->nullable();
      $table->string('tipo_docente');
      $table->string('docente_categoria');
      $table->string('clase');
      $table->string('horas', 10);
      $table->string('orcid');
      $table->string('google_scholar')->nullable();
      $table->string('cti_vitae');
      $table->string('renacyt');
      $table->string('renacyt_nivel');
      $table->string('d1')->default('');
      $table->string('d2')->default('');
      $table->text('d3')->default('');
      $table->text('d4')->default('');
      $table->text('d5')->default('');
      $table->string('d6')->default('');
      $table->boolean('confirmar')->nullable();
      $table->text('confirmar_descripcion')->nullable();
      $table->string('estado')->nullable();
      $table->string('estado_tecnico')->nullable();
      $table->string('estado_real')->nullable();
      $table->date('fecha_envio_mail')->nullable();
      $table->timestamps();

      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
      $table->foreign('facultad_id')->references('id')->on('Facultad');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Eval_docente_investigador');
  }
};
