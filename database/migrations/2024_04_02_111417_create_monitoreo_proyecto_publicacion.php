<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Monitoreo_proyecto_publicacion', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('monitoreo_proyecto_id');
      $table->unsignedBigInteger('publicacion_id');
      $table->timestamps();

      //  Fks
      $table->foreign('monitoreo_proyecto_id')->references('id')->on('Monitoreo_proyecto');
      $table->foreign('publicacion_id')->references('id')->on('Publicacion');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Monitoreo_proyecto_publicacion');
  }
};
