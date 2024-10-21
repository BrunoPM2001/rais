<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel {
  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void {
    //  Cronjobs
    $schedule
      ->call(function () {
        try {

          DB::table('Eval_docente_investigador')
            ->where('tipo_eval', '=', 'Constancia')
            ->where('id', '=', 965)
            // ->where(DB::raw('DATE(fecha_fin)'), '<', Carbon::now())
            // ->where('estado_real', '!=', 'NO VIGENTE')
            ->update([
              'estado_real' => 'NO VIGENTE'
            ]);
          Log::info('OK cronjob');
        } catch (\Exception $e) {
          Log::error('Error en cronjob: ' . $e->getMessage());
        }
      })
      ->everyMinute();
  }

  /**
   * Register the commands for the application.
   */
  protected function commands(): void {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}
