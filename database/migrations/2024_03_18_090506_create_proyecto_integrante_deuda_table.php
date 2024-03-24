<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_integrante_deuda', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_integrante_id')->nullable(); //  Tabla actual
      $table->unsignedBigInteger('proyecto_integrante_h_id')->nullable(); //  Tabla histórica
      $table->integer('tipo')->default(0);
      $table->string('categoria', 50)->default("");
      $table->string('informe', 100)->nullable();
      $table->string('detalle')->nullable();
      $table->string('periodo', 10)->nullable();
      $table->date('fecha_deuda')->nullable();
      $table->date('fecha_sub')->nullable();
      $table->boolean('estado')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('proyecto_integrante_id')->references('id')->on('Proyecto_integrante');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_integrante_deuda');
  }
};
