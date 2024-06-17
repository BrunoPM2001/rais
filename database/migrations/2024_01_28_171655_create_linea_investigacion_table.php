<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Linea_investigacion', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('facultad_id')->nullable();
      $table->unsignedBigInteger('parent_id')->nullable();
      $table->string('codigo')->unique();
      $table->string('nombre');
      $table->string('resolucion')->nullable();
      $table->boolean('estado')->default(true);
      $table->timestamps();
      //  Fks
      $table->foreign('facultad_id')->references('id')->on('Facultad');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Linea_investigacion');
  }
};
