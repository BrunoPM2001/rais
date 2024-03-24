<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Laboratorio', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('facultad_id');
      $table->string('laboratorio');
      $table->string('codigo')->nullable();
      $table->string('responsable');
      $table->string('categoria_uso', 20)->nullable();
      $table->string('ubicacion')->nullable();

      //  Fks
      $table->foreign('facultad_id')->references('id')->on('Facultad');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Laboratorio');
  }
};
