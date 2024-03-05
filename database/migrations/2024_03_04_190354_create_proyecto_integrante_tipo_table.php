<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_integrante_tipo', function (Blueprint $table) {
      $table->id();
      $table->string('nombre');
      $table->string('tipo_proyecto', 50);
      $table->boolean('requerido')->default(0);
      $table->integer('minimo')->default(1);
      $table->integer('maximo')->default(1);
      $table->string('perfil')->nullable();
      $table->string('condicion')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_integrante_tipo');
  }
};
