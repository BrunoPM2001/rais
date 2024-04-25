<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Patente_autor', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('patente_id');
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->string('condicion', 100)->nullable();
      $table->boolean('es_presentador')->nullable();
      $table->string('nombres', 100);
      $table->string('apellido1', 100);
      $table->string('apellido2', 100);
      $table->decimal('puntaje', 5, 2)->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('patente_id')->references('id')->on('Patente');
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Patente_autor');
  }
};
