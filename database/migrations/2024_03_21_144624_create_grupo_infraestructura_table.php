<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    //  TODO - En la tabla de laboratorio registrar los laboratorios de esta tabla - migrar archivos a minio
    Schema::create('Grupo_infraestructura', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('grupo_id');
      $table->unsignedBigInteger('laboratorio_id')->nullable();
      $table->string('categoria', 50);
      $table->string('codigo', 20)->nullable();
      $table->string('nombre')->nullable();
      $table->text('descripcion')->nullable();
      $table->decimal('area_mt2', 10)->nullable();
      $table->string('res_decanal')->nullable();
      $table->text('contacto')->nullable();
      $table->string('ubicacion')->nullable();
      $table->decimal('valor_estimado', 10)->nullable();
      $table->unsignedBigInteger('parent_id')->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign('grupo_id')->references('id')->on('Grupo');
      $table->foreign('laboratorio_id')->references('id')->on('Laboratorio');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Grupo_infraestructura');
  }
};
