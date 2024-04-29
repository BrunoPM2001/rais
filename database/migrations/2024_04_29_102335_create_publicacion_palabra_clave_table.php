<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Publicacion_palabra_clave', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('publicacion_id');
      $table->string('clave', 300);

      //  Fks
      $table->foreign('publicacion_id')->references('id')->on('Publicacion');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Publicacion_palabra_clave');
  }
};
