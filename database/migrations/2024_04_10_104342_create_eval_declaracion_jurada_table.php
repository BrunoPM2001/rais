<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Eval_declaracion_jurada', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('investigador_id');
      $table->dateTime('fecha_inicio');
      $table->dateTime('fecha_fin');
      $table->timestamps();

      //  Fks
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Eval_declaracion_jurada');
  }
};
