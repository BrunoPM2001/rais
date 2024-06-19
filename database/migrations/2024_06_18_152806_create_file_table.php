<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('File', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('tabla_id');
      $table->string('tabla');
      $table->string('bucket');
      $table->string('key');
      $table->string('recurso');
      $table->tinyInteger('estado')->default(1);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('File');
  }
};
