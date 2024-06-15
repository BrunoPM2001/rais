<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Geco_operacion', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('geco_proyecto_id');
      $table->tinyInteger('estado');
      $table->text('observacion');
      $table->text('justificacion');
      $table->dateTime('fecha_aprobado');
      $table->timestamps();

      //  Fks
      $table->foreign('geco_proyecto_id')->references('id')->on('Geco_proyecto');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Geco_operacion');
  }
};
