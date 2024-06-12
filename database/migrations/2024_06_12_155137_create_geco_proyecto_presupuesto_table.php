<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Geco_proyecto_presupuesto', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('geco_proyecto_id');
      $table->unsignedBigInteger('partida_id');
      $table->tinyInteger('partida_nueva')->nullable();
      $table->decimal('monto', 10, 2)->default(0);
      $table->decimal('monto_temporal', 10, 2)->nullable();
      $table->decimal('monto_rendido_enviado', 10, 2)->nullable();
      $table->decimal('monto_rendido', 10, 2)->nullable();
      $table->decimal('monto_excedido', 10, 2)->nullable();
      $table->tinyInteger('estado');
      $table->nullableTimestamps();

      //  Fks
      $table->foreign('geco_proyecto_id')->references('id')->on('Geco_proyecto');
      $table->foreign('partida_id')->references('id')->on('Partida');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Geco_proyecto_presupuesto');
  }
};
