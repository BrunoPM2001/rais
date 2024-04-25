<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Patente', function (Blueprint $table) {
      $table->id();
      $table->string('nro_registro', 50)->nullable();
      $table->string('tipo', 50);
      $table->string('titulo', 300);
      $table->string('titular1', 300)->nullable();
      $table->string('titular2', 300)->nullable();
      $table->text('comentario')->nullable();
      $table->text('observaciones_usuario')->nullable();
      $table->string('nro_expediente', 100)->nullable();
      $table->date('fecha_presentacion')->nullable();
      $table->string('oficina_presentacion', 100)->nullable();
      $table->string('enlace')->nullable();
      $table->tinyInteger('step');
      $table->tinyInteger('estado');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Patente');
  }
};
