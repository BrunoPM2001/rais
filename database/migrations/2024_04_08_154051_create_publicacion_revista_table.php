<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Publicacion_revista', function (Blueprint $table) {
      $table->id();
      $table->string('issn', 20)->nullable();
      $table->string('issne', 100)->nullable();
      $table->string('revista', 300)->nullable();
      $table->string('casa', 300)->nullable();
      $table->integer('isi')->nullable();
      $table->string('pais', 50)->nullable();
      $table->string('cobertura', 50)->nullable();
      $table->boolean('estado')->nullable();
      $table->nullableTimestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Publicacion_revista');
  }
};
