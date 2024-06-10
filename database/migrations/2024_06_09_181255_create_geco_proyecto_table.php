<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Geco_proyecto', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('proyecto_id');
      $table->decimal('total', 10, 2)->default(0.00);
      $table->boolean('estado')->default(0);
      $table->timestamps();

      //  Fks
      $table->foreign('proyecto_id')->references('id')->on('Proyecto');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Geco_proyecto');
  }
};
