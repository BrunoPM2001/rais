<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    //  TODO - eliminar las columnas de grupo_id e investigador_id y reemplazarla por grupo_integrante_id
    Schema::create('Grupo_integrante_doc', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('grupo_id');
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->string('nombre', 150)->nullable();
      $table->string('key');
      $table->date('fecha');
      $table->boolean('estado');

      //  Fks
      $table->foreign('grupo_id')->references('id')->on('Grupo');
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Grupo_integrante_doc');
  }
};
