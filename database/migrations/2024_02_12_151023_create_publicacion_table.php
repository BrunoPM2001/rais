<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Publicacion', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger("categoria_id")->nullable();
      $table->string("codigo_registro", 9)->nullable();
      $table->string("isbn", 50)->nullable();
      $table->string("issn", 50)->nullable();
      $table->string("issn_e", 15)->nullable();
      $table->string("doi", 150)->nullable();
      $table->text("uri")->nullable();
      $table->text("titulo")->nullable();
      $table->binary("resumen")->nullable();
      $table->string("publicacion_nombre", 250)->nullable();
      $table->string("evento_nombre", 250)->nullable();
      $table->string("volumen", 20)->nullable();
      $table->string("edicion", 20)->nullable();
      $table->string("editorial", 250)->nullable();
      $table->string("editor", 100)->nullable();
      $table->string("pais_codigo", 4)->nullable();
      $table->string("pais", 50)->nullable();
      $table->string("lugar_publicacion", 100)->nullable();
      $table->string("universidad", 250)->nullable();
      $table->string("pagina_inicial", 10)->nullable();
      $table->string("pagina_final", 10)->nullable();
      $table->string("pagina_total", 20)->nullable();
      $table->date("fecha_publicacion")->nullable();
      $table->date("fecha_inicio")->nullable();
      $table->date("fecha_fin")->nullable();
      $table->dateTime("fecha_inscripcion")->nullable();
      //  TODO - POR QUITAR CUANDO LA COLUMNA DE CATEGORIA_ID ESTÉ BIEN
      $table->string("tipo_publicacion", 50);
      $table->string("formato", 50)->nullable();
      $table->text("comentario")->nullable();
      $table->text("observaciones_usuario")->nullable();
      $table->boolean("validado")->nullable();
      $table->string("url", 250)->nullable();
      $table->mediumInteger("step")->nullable();
      $table->mediumInteger("estado")->nullable();
      $table->string("tipo_tesis")->nullable();
      $table->string("libro_resumen")->nullable();
      $table->string("ciudad_edicion")->nullable();
      $table->string("ciudad")->nullable();
      $table->string("tipo_presentacion")->nullable();
      $table->string("nombre_evento")->nullable();
      $table->string("tipo_participacion")->nullable();
      $table->string("publicacion_indexada")->nullable();
      $table->year("year_publicacion")->nullable();
      $table->string("tipo_patente")->nullable();
      $table->text("repositorio_tesis")->nullable();
      $table->string("nombre_libro")->nullable();
      $table->string("art_tipo")->nullable();
      $table->string("idioma")->nullable();
      $table->string("source")->nullable();
      $table->string("tipo_doc")->nullable();
      $table->text("audit")->nullable();
      $table->timestamps();

      //  FKS
      $table->foreign('categoria_id')->references('id')->on('Publicacion_categoria');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Publicacion');
  }
};
