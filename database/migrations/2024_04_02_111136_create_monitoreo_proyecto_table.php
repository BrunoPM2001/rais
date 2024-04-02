<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Monitoreo_proyecto', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('meta_periodo_id');
      $table->unsignedBigInteger('proyecto_id');
      $table->text('descripcion')->nullable();
      $table->text('observacion')->nullable();
      $table->date('fecha_envio');
      $table->date('fecha_aprobacion');
      $table->tinyInteger('estado');
      $table->timestamps();

      //  Fks
      $table->foreign('meta_periodo_id')->references('id')->on('Meta_periodo');
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Monitoreo_proyecto');
  }
};
