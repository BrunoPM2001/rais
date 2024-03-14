<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Partida', function (Blueprint $table) {
      $table->id();
      $table->string('codigo', 15);
      $table->string('partida', 150);
      $table->string('descripcion', 300)->nullable();
      $table->string('tipo', 10)->nullable();
      $table->integer('level');
      $table->unsignedBigInteger('parent_id');
      $table->boolean('estado');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Partida');
  }
};
