<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Licencia', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('licencia_tipo_id');
      $table->unsignedBigInteger('investigador_id');
      $table->date('fecha_inicio');
      $table->date('fecha_fin');
      $table->text('comentario');
      $table->text('documento');
      $table->string('user_create', 50)->default("");
      $table->string('user_edit', 50)->default("");

      //  Fks
      $table->foreign('licencia_tipo_id')->references('id')->on('Licencia_tipo');
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Licencia');
  }
};
