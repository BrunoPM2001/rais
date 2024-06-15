<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Facultad', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('area_id');
      $table->string('nombre')->unique();
      $table->timestamps();
      //  Fks
      $table->foreign('area_id')->references('id')->on('Area');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Facultad');
  }
};
