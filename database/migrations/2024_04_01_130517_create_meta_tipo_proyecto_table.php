<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Meta_tipo_proyecto', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('meta_periodo_id');
      $table->string('tipo_proyecto');
      $table->boolean('estado')->default(1);
      $table->timestamps();

      //  Fks
      $table->foreign('meta_periodo_id')->references('id')->on('Meta_periodo');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Meta_tipo_proyecto');
  }
};
