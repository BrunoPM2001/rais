<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Repo_sum', function (Blueprint $table) {
      $table->id();
      $table->string('codigo_alumno', 10)->nullable();
      $table->string('nombres', 40)->nullable();
      $table->string('apellido_paterno', 40)->nullable();
      $table->string('apellido_materno', 40)->nullable();
      $table->string('dni', 15)->nullable();
      $table->string('sexo', 50)->nullable();
      $table->string('fecha_nacimiento', 10)->nullable();
      $table->string('lugar_nacimiento', 15)->nullable();
      $table->string('telefono', 25)->nullable();
      $table->string('telefono_personal', 25)->nullable();
      $table->string('correo_electronico', 50)->nullable();
      $table->string('correo_electronico_personal', 50)->nullable();
      $table->string('domicilio', 150)->nullable();
      $table->string('id_facultad', 50)->nullable();
      $table->string('facultad', 80)->nullable();
      $table->string('especialidad', 50)->nullable();
      $table->string('programa', 300)->nullable();
      $table->string('año_ciclo_estudio', 50)->nullable();
      $table->string('num_periodo_acad_matric', 50)->nullable();
      $table->string('promedio_ponderado', 50)->nullable();
      $table->string('situacion_academica', 30)->nullable();
      $table->string('permanencia', 30)->nullable();
      $table->string('ultimo_periodo_matriculado', 50)->nullable();
      $table->string('promedio_ultima_matricula', 50)->nullable();
      $table->string('año_ingreso', 10)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Repo_sum');
  }
};
