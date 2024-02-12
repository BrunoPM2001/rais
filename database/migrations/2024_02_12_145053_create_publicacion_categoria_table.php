<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Publicacion_categoria', function (Blueprint $table) {
      $table->id();
      $table->string("rr")->nullable();
      $table->string("tipo");
      $table->string("categoria");
      $table->string("titulo");
      $table->decimal("puntaje");
      $table->boolean("compartir")->default(false);
      $table->decimal("monto_rec")->default("0.00");
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Publicacion_categoria');
  }
};
