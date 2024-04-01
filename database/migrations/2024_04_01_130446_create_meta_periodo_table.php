<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Meta_periodo', function (Blueprint $table) {
      $table->id();
      $table->year('periodo');
      $table->string('descripcion')->nullable();
      $table->boolean('estado')->default(1);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Meta_periodo');
  }
};
