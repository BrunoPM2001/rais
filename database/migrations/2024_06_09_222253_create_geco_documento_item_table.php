<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Geco_documento_item', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('geco_documento_id');
      $table->unsignedBigInteger('partida_id');
      $table->decimal('monto', 10, 3)->nullable();
      $table->decimal('igv', 10, 3)->default(0);
      //  START - Columnas no usadas de momento
      $table->integer('dni')->nullable();
      //  END
      $table->decimal('retencion', 10, 3)->nullable();
      $table->decimal('monto_excedido', 10, 3)->nullable();
      $table->decimal('sub_total', 10, 3)->default(0);
      $table->decimal('total', 10, 3)->default(0);
      $table->decimal('igv_tasa', 10, 3)->default(0);
      $table->boolean('sin_igv')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('geco_documento_id')->references('id')->on('Geco_documento');
      $table->foreign('partida_id')->references('id')->on('Partida');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Geco_documento_item');
  }
};
