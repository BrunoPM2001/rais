<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Publicacion_proyecto', function (Blueprint $table) {
      //  Todo verificar las FKS
      $table->id();
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->unsignedBigInteger('publicacion_id');
      $table->unsignedBigInteger('proyecto_id')->nullable();
      $table->unsignedBigInteger('proyecto_h_id')->nullable();
      $table->string('tipo', 50);
      $table->string('codigo_proyecto', 150)->nullable();
      $table->text('nombre_proyecto');
      $table->string('entidad_financiadora', 150)->nullable();
      $table->tinyInteger('estado');
      $table->timestamps();

      //  Fks
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
      $table->foreign('publicacion_id')->references('id')->on('Publicacion');
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
      $table->foreign('proyecto_h_id')->references('id')->on('Proyecto_H');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Publicacion_proyecto');
  }
};
