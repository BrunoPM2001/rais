<?php

namespace App\Console\Commands\Admin\Estudios;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ActualizarCdiNoVigente extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:actualizar-cdi-no-vigente';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Cambiar de estado constancias no vigentes';

  /**
   * Execute the console command.
   */
  public function handle() {
    DB::table('Eval_docente_investigador')
      ->where('tipo_eval', '=', 'Constancia')
      ->where(DB::raw('DATE(fecha_fin)'), '<', Carbon::now())
      ->where('estado', '!=', 'NO VIGENTE')
      ->update([
        'estado' => 'NO VIGENTE'
      ]);

    $this->newLine();
    $this->line("  <bg=green;fg=white> SUCCESS </> Constancias no vigentes actualizadas");
    $this->newLine();
  }
}
