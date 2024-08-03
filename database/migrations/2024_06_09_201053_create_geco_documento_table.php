<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Geco_documento', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('geco_proyecto_id');
      $table->unsignedBigInteger('investigador_id')->nullable();
      $table->string('tipo', 50);
      $table->string('numero', 50)->nullable();
      $table->string('ruc', 20)->nullable();
      $table->date('fecha');
      $table->string('dni_pasajero', 12)->nullable();
      $table->string('concepto')->nullable();
      $table->string('razon_social')->nullable();
      $table->float('monto')->default(0);
      $table->string('tipo_moneda')->nullable();
      $table->string('descripcion_compra')->nullable();
      $table->string('pais_emisor')->nullable();
      //  START - No se utilizan aún las siguientes columnas
      $table->string('acepta_excedido')->default(0)->nullable();
      $table->float('monto_excedido')->default(0)->nullable();
      //  END
      $table->string('detraccion')->nullable();
      $table->string('datos_pasajero')->nullable();
      $table->string('numero_doc')->nullable();
      $table->string('prestador')->nullable();
      $table->float('monto_exterior')->nullable();
      $table->string('viatico')->nullable();
      $table->string('ciudad_origen')->nullable();
      $table->decimal('total_sin_igv', 10, 3)->default(0);
      $table->decimal('total_calculado', 10, 3)->default(0);
      $table->decimal('total_declarado', 10, 3)->default(0);
      $table->date('fecha_presentacion_solicitud_compra')->nullable();
      $table->tinyInteger('estado');
      $table->timestamps();
      //  Columna temporal
      $table->text('observacion');
      $table->text('audit');
      //  Fks
      $table->foreign('geco_proyecto_id')->references('id')->on('Geco_proyecto');
      $table->foreign('investigador_id')->references('id')->on('Usuario_investigador');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Geco_documento');
  }
};
