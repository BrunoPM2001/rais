<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Eval_docente_actividad', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('investigador_id');
      $table->unsignedBigInteger('eval_docente_investigador_id')->nullable();
      $table->unsignedBigInteger('categoria_id')->nullable();
      $table->unsignedBigInteger('sub_categoria_id')->nullable();
      $table->string('tipo');
      $table->tinyInteger('estado');
      $table->timestamps();

      //  Fks
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
      $table->foreign('eval_docente_investigador_id')->references('id')->on('Eval_docente_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Eval_docente_actividad');
  }
};
