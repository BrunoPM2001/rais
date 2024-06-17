<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Linea_investigacion_ods', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('linea_investigacion_id');
      $table->unsignedBigInteger('ods_id');

      //  Fks
      $table->foreign('linea_investigacion_id')->references('id')->on('Linea_investigacion');
      $table->foreign('ods_id')->references('id')->on('Ods');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Linea_investigacion_ods');
  }
};
