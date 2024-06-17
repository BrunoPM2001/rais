<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Ods', function (Blueprint $table) {
      $table->id();
      $table->string('objetivo');
      $table->string('descripcion')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Ods');
  }
};
