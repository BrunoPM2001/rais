<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Patente_entidad', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('patente_id');
      $table->string('titular');
      $table->timestamps();

      //  Fks
      $table->foreign('patente_id')->references('id')->on('Patente');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Patente_entidad');
  }
};
