<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Proyecto_fex_doc', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_id');
      $table->string('doc_tipo', 10);
      $table->string('nombre', 150)->nullable();
      $table->string('comentario')->nullable();
      $table->string('bucket');
      $table->string('key');
      $table->date('fecha');

      //  Fks
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Proyecto_fex_doc');
  }
};
