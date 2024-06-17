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
      $table->decimal("puntaje", 10, 2);
      $table->boolean("compartir")->default(false);
      $table->decimal("monto_rec", 10, 2)->default(0);
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
