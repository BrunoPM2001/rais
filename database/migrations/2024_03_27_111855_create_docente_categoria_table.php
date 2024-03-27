<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Docente_categoria', function (Blueprint $table) {
      $table->id();
      $table->string('categoria_id')->unique();
      $table->string('categoria', 50);
      $table->string('clase', 50);
      $table->smallInteger('horas')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Docente_categoria');
  }
};
