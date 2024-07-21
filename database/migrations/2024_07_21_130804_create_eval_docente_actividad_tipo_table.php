<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Eval_docente_actividad_tipo', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('parent_id')->nullable();
      $table->string('codigo');
      $table->string('categoria');
      $table->string('descripcion');
      $table->tinyInteger('nivel');
      $table->tinyInteger('estado');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Eval_docente_actividad_tipo');
  }
};
