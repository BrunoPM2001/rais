<?php

namespace App\Console\Commands\Admin;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpdateRrhh extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:update-rrhh';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Actualizar la tabla de RRHH en base a una api';

  /**
   * Execute the console command.
   */
  public function handle() {

    $this->newLine();
    $this->warn(" Realizando peticición...");
    $response = Http::get("https://talenthum-unmsm.site/sistema/webservice/vrip_masivo.php");

    if ($response->successful()) {

      $this->info(" Petición exitosa...");
      $this->newLine();

      $bod = substr($response->body(), 3);
      $rawData = json_decode($bod);

      $this->withProgressBar(
        $rawData,
        function ($item) {
          DB::table('Repo_rrhh')
            ->updateOrInsert([
              'ser_doc_id_act' => $item->dni
            ], [
              'ser_ape_pat' => $item->apaterno,
              'ser_ape_mat' => $item->amaterno,
              'ser_nom' => $item->nombres,
              'ser_fech_nac' => $item->fechanacimiento,
              'ser_fech_in_unmsm' => $item->fechaingreso,
              'des_dep_cesantes' => $item->nombre_facultad,
              'ser_cat_act' => $item->abreviatura_categoria,
              'ser_cod_dep_ces' => $item->codigo_facultad,
              'updated_at' => Carbon::now()
            ]);
        }
      );

      $this->newLine(2);
    }
  }
}
