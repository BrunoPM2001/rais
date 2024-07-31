<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Eval_docente_investigador_obs', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('eval_investigador_id');
      $table->text('observacion');
      $table->timestamps();

      //  Fk
      $table->foreign('eval_investigador_id')->references('id')->on('Eval_docente_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Eval_docente_investigador_obs');
  }
};
