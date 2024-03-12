<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_presupuesto', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_id');
      $table->unsignedBigInteger('partida_id');
      $table->string('tipo', 50);
      $table->integer('partida_nueva')->nullable();
      $table->decimal('monto', 10)->default(0.00)->nullable();
      $table->decimal('monto_temporal', 10)->nullable();
      $table->decimal('monto_excedido', 10)->nullable();
      $table->decimal('monto_rendido', 10)->nullable();
      $table->decimal('monto_rendido_enviado', 10)->nullable();
      $table->integer('estado')->nullable();
      $table->text('justificacion')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
      $table->foreign('partida_id')->references('id')->on('Partida');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_presupuesto');
  }
};
