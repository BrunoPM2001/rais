<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Geco_operacion_movimiento', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('geco_operacion_id');
      $table->unsignedBigInteger('geco_proyecto_presupuesto_id');
      $table->string('operacion', '1');
      $table->decimal('monto', 10, 2);
      $table->string('monto_original', 10, 2);
      $table->tinyInteger('estado');
      $table->timestamps();

      //  Fks
      $table->foreign('geco_operacion_id')->references('id')->on('Geco_operacion');
      $table->foreign('geco_proyecto_presupuesto_id')->references('id')->on('Geco_proyecto_presupuesto');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Geco_operacion_movimiento');
  }
};
