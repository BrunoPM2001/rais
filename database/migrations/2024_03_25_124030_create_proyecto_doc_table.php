<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_doc', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_id');
      $table->unsignedBigInteger('tipo')->nullable();
      $table->string('categoria');
      $table->string('nombre');
      $table->string('comentario')->nullable();
      $table->string('ruta')->nullable();
      $table->boolean('estado');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_doc');
  }
};
