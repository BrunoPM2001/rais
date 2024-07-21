<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Actividad_investigador', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('eval_docente_actividad_id');
      $table->string('revista', 500)->nullable();
      $table->string('rol')->nullable();
      $table->string('nombre')->nullable();
      $table->string('dni')->nullable();
      $table->string('periodo')->nullable();
      $table->string('condicion')->nullable();
      $table->string('fecha')->nullable();
      $table->string('autor')->nullable();
      $table->string('estado')->nullable();
      $table->string('titulo')->nullable();
      $table->string('tipo')->nullable();
      $table->string('categoria')->nullable();
      $table->string('url')->nullable();
      $table->string('lugar_act')->nullable();
      $table->string('tipo_transf')->nullable();
      $table->string('aplicacion')->nullable();
      $table->string('beneficiario')->nullable();
      $table->string('num_documento')->nullable();

      //  Fks
      $table->foreign('eval_docente_actividad_id')->references('id')->on('Eval_docente_actividad');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Actividad_investigador');
  }
};
