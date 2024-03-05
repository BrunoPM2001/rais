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
      $table->unsignedBigInteger('proyecto_integrante_tipo')->nullable();
      $table->unsignedBigInteger('grupo_id')->nullable();
      $table->unsignedBigInteger('grupo_integrante_id')->nullable();
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->string('condicion')->nullable();
      //  TODO - Completar campos en base a lo que diga Max
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_integrante');
  }
};
