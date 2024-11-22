<?php

namespace App\Console\Commands\Investigador;

use App\Mail\Investigador\Perfil\CdiPorVencer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RecordarVencimientoCdi extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:recordar-vencimiento-cdi';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Recordatorio por correo del vencimiento de CDI a docentes';

  /**
   * Execute the console command.
   */
  public function handle() {
    $now = Carbon::now();
    $this->newLine();

    $this->withProgressBar(
      DB::table('Eval_docente_investigador AS a')
        ->join('Usuario_investigador AS b', 'b.id', '=', 'a.investigador_id')
        ->select([
          'a.nombres',
          'b.email3 AS email',
          DB::raw("DATE(a.fecha_fin) AS fecha_fin"),
        ])
        ->where('a.tipo_eval', '=', 'Constancia')
        ->where('a.estado', '=', 'Vigente')
        ->where(DB::raw('DATE(a.fecha_fin)'), '<', $now->addMonths(2))
        ->get(),
      function ($investigador) {
        Mail::to($investigador->email)->send(new CdiPorVencer($investigador));
        $this->info(" Correo enviado a " . $investigador->nombres);
      }
    );

    $this->newLine(2);
  }
}
