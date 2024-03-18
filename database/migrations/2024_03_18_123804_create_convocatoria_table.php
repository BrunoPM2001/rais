<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Convocatoria', function (Blueprint $table) {
      $table->id();
      $table->string('tipo')->nullable();
      $table->string('evento');
      $table->date('fecha_inicial');
      $table->date('fecha_final');
      $table->date('fecha_corte');
      $table->year('periodo');
      $table->tinyInteger('convocatoria');
      $table->boolean('estado');
      $table->unsignedBigInteger('parent_id')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Convocatoria');
  }
};
