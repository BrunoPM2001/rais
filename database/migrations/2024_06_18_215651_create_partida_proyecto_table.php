<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Partida_proyecto', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('partida_id');
      $table->string('tipo_proyecto');
      $table->decimal('porcentaje', 10, 2);
      $table->decimal('monto', 10, 2)->nullable();
      $table->tinyInteger('postulacion');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Partida_proyecto');
  }
};
