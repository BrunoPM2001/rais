<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('Repo_rrhh', function (Blueprint $table) {
      $table->id();
      $table->string('ser_cod');
      $table->bigInteger('num_serest')->nullable();
      $table->string('ser_cod_ant', 15);
      $table->string('ser_ape_pat');
      $table->string('ser_ape_mat');
      $table->string('ser_nom');
      $table->string('ser_cat_act');
      $table->string('ser_car_sueldos_act');
      $table->string('cod_tip_pla', 150);
      $table->string('avb_pla');
      $table->bigInteger('pla_mes');
      $table->integer('pla_anu');
      $table->string('cod_pla');
      $table->string('desc_reg_pen');
      $table->string('ser_cta_ban_act');
      $table->string('ser_num_seg_soc');
      $table->decimal('bruto', 10);
      $table->decimal('descuento', 10);
      $table->decimal('neto', 10);
      $table->string('ser_obs_pla_perm', 450);
      $table->string('ser_cod_dep_ces');
      $table->string('des_dep_cesantes');
      $table->string('ser_doc_id_act');
      $table->string('des_ent_aseg');
      $table->string('ser_abv_con_pla');
      $table->string('abv_doc_id');
      $table->string('ser_fech_in_unmsm', 15);
      $table->string('ser_fech_nac', 15);
      $table->string('ser_num_sis_pri_pen');
      $table->string('essalud', 150);
      $table->string('tot_cheque');
      $table->string('num_cheque');
      $table->string('abv_tip_pag_ser');
      $table->string('letra_tip_ser');
      $table->string('des_tip_ser');
      $table->string('letra_est');
      $table->string('desc_est');
      $table->string('ser_sexo', 100);
      $table->string('extra-01');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::drop('Repo_rrhh');
  }
};
