<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Publicacion_autor', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('publicacion_id');
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->string('codigo_orcid', 100)->nullable();
      $table->string('nro_registro', 50)->nullable();
      $table->date('fecha_registro')->nullable();
      $table->string('doc_tipo', 50)->nullable();
      $table->string('doc_numero', 10)->nullable();
      $table->string('autor', 150)->nullable();
      $table->string('nombres', 100)->nullable();
      $table->string('apellido1', 50)->nullable();
      $table->string('apellido2', 50)->nullable();
      $table->string('tipo', 10)->nullable();
      $table->string('categoria', 50)->nullable();
      $table->boolean('presentado')->nullable();
      $table->integer('orden')->nullable();
      //  Por quitar
      $table->decimal('puntaje')->nullable();
      $table->boolean('filiacion')->nullable();
      $table->boolean('filiacion_unica')->nullable();
      $table->boolean('estado')->default(false);
      $table->timestamps();

      //  Fks
      $table->foreign('publicacion_id')->references('id')->on('Publicacion');
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Publicacion_autor');
  }
};
