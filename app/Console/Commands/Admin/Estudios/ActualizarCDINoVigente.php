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

    // 1. Cambiar a "No vigente" constancias cuya fecha_fin ya expiró
    DB::table('Eval_docente_investigador')
      ->where('tipo_eval', '=', 'Constancia')
      ->where(DB::raw('DATE(fecha_fin)'), '<', Carbon::now())
      ->where('estado', '!=', 'No vigente')
      ->update([
        'estado' => 'No vigente'
      ]);

    // 2. Cambiar a "No vigente" constancias anteriores cuando exista una nueva en estado 'Vigente'
    $idsConstanciasSuperadas = DB::table('Eval_docente_investigador AS a')
      ->join('Eval_docente_investigador AS b', function ($join) {
        $join->on('a.investigador_id', '=', 'b.investigador_id')
          ->where(function ($query) {
            $query->whereColumn('b.fecha_constancia', '>', 'a.fecha_constancia')
              ->orWhere(function ($q) {
                $q->whereColumn('b.fecha_constancia', '=', 'a.fecha_constancia')
                  ->whereColumn('b.id', '>', 'a.id');
              });
          });
      })
      ->where('a.tipo_eval', '=', 'Constancia')
      ->where('b.tipo_eval', '=', 'Constancia')
      ->where('a.estado', '!=', 'No vigente')
      ->where('b.estado', '=', 'Vigente')
      ->pluck('a.id');
      
    if ($idsConstanciasSuperadas->isNotEmpty()) {
      DB::table('Eval_docente_investigador')
        ->whereIn('id', $idsConstanciasSuperadas)
        ->update([
          'estado' => 'No vigente'
        ]);
    }

    $this->newLine();
    $this->line("  <bg=green;fg=white> SUCCESS </> Constancias no vigentes actualizadas");
    $this->newLine();
  }
}
