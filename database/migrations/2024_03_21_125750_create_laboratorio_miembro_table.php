<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Laboratorio_miembro', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('laboratorio_id');
      $table->string('nombre');

      //  Fks
      $table->foreign('laboratorio_id')->references('id')->on('Laboratorio');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Laboratorio_miembro');
  }
};
