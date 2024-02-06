<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Usuario', function (Blueprint $table) {
      $table->id();
      $table->string("username")->nullable();
      $table->string("email")->nullable();
      $table->string("password");
      $table->string("tabla");
      $table->unsignedBigInteger("tabla_id");
      $table->boolean("estado")->default(true);
      $table->rememberToken();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Usuario');
  }
};
