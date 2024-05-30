<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Pais', function (Blueprint $table) {
      $table->id();
      $table->string('code', 2)->unique();
      $table->string('iso_code_3', 3);
      $table->string('name', 60);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Pais');
  }
};
