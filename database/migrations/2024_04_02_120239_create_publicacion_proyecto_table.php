<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Publicacion_proyecto', function (Blueprint $table) {
      //  Todo verificar las FKS
      $table->id();
      $table->unsignedBigInteger('publicacion_id');
      $table->unsignedBigInteger('proyecto_id');
      $table->tinyInteger('estado');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Publicacion_proyecto');
  }
};
