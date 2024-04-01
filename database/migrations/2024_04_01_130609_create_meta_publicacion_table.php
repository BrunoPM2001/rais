<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Meta_publicacion', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('meta_tipo_proyecto_id');
      $table->string('tipo_publicacion');
      $table->integer('cantidad')->nullable();
      $table->boolean('estado')->default(1);
      $table->timestamps();

      //  Fks
      $table->foreign('meta_tipo_proyecto_id')->references('id')->on('Meta_tipo_proyecto');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Meta_publicacion');
  }
};
