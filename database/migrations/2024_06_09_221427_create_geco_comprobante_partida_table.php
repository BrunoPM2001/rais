<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Geco_comprobante_partida', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('geco_comprobante_id');
      $table->unsignedBigInteger('partida_id');
      $table->integer('periodo');
      $table->boolean('estado');

      //  Fks
      $table->foreign('geco_comprobante_id')->references('id')->on('Geco_comprobante');
      $table->foreign('partida_id')->references('id')->on('Partida');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Geco_comprobante_partida');
  }
};
