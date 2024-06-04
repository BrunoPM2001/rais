<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Publicacion_index', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('publicacion_id');
      $table->unsignedBigInteger('publicacion_db_indexada_id');
      $table->timestamps();

      //  Fks
      $table->foreign('publicacion_id')->references('id')->on('Publicacion');
      $table->foreign('publicacion_db_indexada_id')->references('id')->on('Publicacion_db_indexada');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Publicacion_index');
  }
};
