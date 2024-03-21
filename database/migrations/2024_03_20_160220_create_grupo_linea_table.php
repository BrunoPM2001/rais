<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Grupo_linea', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('grupo_id');
      $table->unsignedBigInteger('linea_investigacion_id')->nullable();
      $table->integer('concytec_codigo')->nullable();

      //  Fks
      $table->foreign('grupo_id')->references('id')->on('Grupo');
      $table->foreign('linea_investigacion_id')->references('id')->on('Linea_investigacion');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Grupo_linea');
  }
};
