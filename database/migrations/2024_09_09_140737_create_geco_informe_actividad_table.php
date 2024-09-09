<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Geco_informe_actividad', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('geco_informe_id');
      $table->unsignedBigInteger('proyecto_integrante_id');
      $table->text('actividad')->nullable();
      $table->boolean('cumplimiento');
      $table->timestamps();

      //  Fks
      $table->foreign('geco_informe_id')->references('id')->on('Geco_informe');
      $table->foreign('proyecto_integrante_id')->references('id')->on('Proyecto_integrante');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Geco_informe_actividad');
  }
};
