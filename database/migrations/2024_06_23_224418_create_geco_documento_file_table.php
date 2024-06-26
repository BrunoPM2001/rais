<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Geco_documento_file', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('geco_documento_id');
      $table->string('key');
      $table->timestamps();

      //  Fks
      $table->foreign('geco_documento_id')->references('id')->on('Geco_documento');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Geco_documento_file');
  }
};
