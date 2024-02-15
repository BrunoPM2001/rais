<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Grupo', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger("facultad_id");
      $table->string("tipo", 100);
      $table->string("grupo_nombre", 500);
      $table->string("grupo_nombre_corto", 10);
      $table->text("presentacion")->nullable();
      $table->text("objetivos")->nullable();
      $table->text("servicios")->nullable();
      $table->string("publicaciones_externos", 150)->nullable();
      $table->text("infraestructura_ambientes")->nullable();
      $table->text("infraestructura_equipamiento")->nullable();
      $table->string("infraestructura_sgestion", 150)->nullable();
      $table->string("telefono", 150)->nullable();
      $table->string("anexo", 150)->nullable();
      $table->string("oficina", 450)->nullable();
      $table->string("direccion", 500)->nullable();
      $table->string("email", 100)->nullable();
      $table->string("web", 500)->nullable();
      $table->integer("step")->nullable();
      $table->text("observaciones")->nullable();
      $table->text("observaciones_admin")->nullable();
      $table->string("resolucion_rectoral", 50)->nullable();
      $table->date("resolucion_fecha")->nullable();
      $table->integer("grupo_rr")->nullable();
      $table->string("resolucion_rectoral_creacion", 50)->nullable();
      $table->date("resolucion_creacion_fecha")->nullable();
      $table->integer("estado")->nullable();
      $table->boolean("edit")->nullable();
      $table->string("grupo_categoria", 50)->nullable();
      $table->date("fecha_disolucion")->nullable();
      $table->text("motivo_disolucion")->nullable();
      $table->integer("objetivo_ods")->nullable();
      $table->boolean("edit_lineas")->nullable();
      $table->integer("coorddatos")->nullable();
      $table->timestamps();

      //  Fks
      $table->foreign("facultad_id")->references("id")->on("Facultad");
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Grupo');
  }
};
