<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Facultad_programa', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('facultad_id');
      $table->unsignedBigInteger('parent_id')->nullable();
      $table->string('programa');
      $table->string('tipo');
      $table->string('categoria');
      $table->tinyInteger('nivel');
      $table->string('path');

      //  Fk
      $table->foreign('facultad_id')->references('id')->on('Facultad');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Facultad_programa');
  }
};
