<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_doc_template', function (Blueprint $table) {
      $table->id();
      $table->string('categoria');
      $table->string('nombre');
      $table->boolean('estado');
      $table->boolean('requerido');
      $table->string('plantilla')->default("");
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_doc_template');
  }
};
