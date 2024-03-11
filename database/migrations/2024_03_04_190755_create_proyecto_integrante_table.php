<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_integrante', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_id');
      $table->unsignedBigInteger('proyecto_integrante_tipo_id')->nullable();
      $table->unsignedBigInteger('grupo_id')->nullable();
      $table->unsignedBigInteger('grupo_integrante_id')->nullable();
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->string('condicion')->nullable();
      $table->string('tipo_tesista', 50)->nullable();
      $table->string('tipo_tesis', 150)->nullable();
      $table->text('titulo_tesis')->nullable();
      $table->text('responsabilidad')->nullable();
      $table->string('contribucion')->nullable();
      $table->string('condicion_grupo')->nullable();
      $table->string('tipo_investigador')->nullable();
      $table->boolean('excluido')->nullable();
      $table->date('fecha_excluido')->nullable();
      $table->string('res_dec_excluido')->nullable();
      $table->date('res_dec_fecha_excluido')->nullable();
      $table->string('res_rec_excluido')->nullable();
      $table->date('res_rec_fecha_excluido')->nullable();
      $table->text('info_exluido')->nullable();
      $table->boolean('estado')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
      $table->foreign('proyecto_integrante_tipo_id')->references('id')->on('Proyecto_integrante_tipo');
      $table->foreign('grupo_id')->references('id')->on('Grupo');
      $table->foreign('grupo_integrante_id')->references('id')->on('Grupo_integrante');
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_integrante');
  }
};
