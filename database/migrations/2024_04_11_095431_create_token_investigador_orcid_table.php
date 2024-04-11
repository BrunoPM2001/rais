<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Token_investigador_orcid', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->string('orcid')->nullable();
      $table->string('access_token');
      $table->string('refresh_token');
      $table->dateTime('expires_in');
      $table->string('scope', 50);
      $table->integer('items_upload')->nullable();

      //  Fks
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Token_investigador_orcid');
  }
};
