<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DatabaseBackup extends Command
{
    protected $signature = 'app:backup-db 
                            {--compress : Comprimir el resultado (.gz)} 
                            {--keep=7 : Mantener N backups más recientes (rotación)} 
                            {--name= : Prefijo de nombre (opcional)} 
                            {--only-table= : Respaldar solo una tabla (opcional)}';

    protected $description = 'Genera un backup de la BD MySQL y lo guarda en storage/app/backups (opcional .gz) con rotación';

    public function handle(): int
    {
        // Lee credenciales desde .env
        $host = env('DB_HOST');
        $port = env('DB_PORT', 3306);
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        $db   = env('DB_DATABASE');

        if (!$host || !$user || !$db) {
            $this->error('Faltan variables DB_* en .env');
            return Command::FAILURE;
        }

        // Carpeta de destino
        $disk = Storage::disk('local');              // storage/app
        $dir  = 'backups';
        if (!$disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }

        // Nombre del archivo
        $prefix = $this->option('name') ?: config('app.name', 'laravel');
        $when   = now()->format('Ymd_His');
        $table  = $this->option('only-table');
        $baseName = $table ? "{$prefix}_{$db}_{$table}_{$when}.sql" : "{$prefix}_{$db}_{$when}.sql";
        $pathRel  = "{$dir}/{$baseName}";
        $pathAbs  = storage_path("app/{$pathRel}");

        // Construir comando mysqldump
        // Nota: --single-transaction evita locks en InnoDB; --quick reduce memoria
        $cmd = [
            'mysqldump',
            '--host=' . $host,
            '--port=' . $port,
            '--user=' . $user,
            // OJO: pasamos el password por variable de entorno para no mostrarlo en la lista de procesos
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--hex-blob',
            $db,
        ];

        if ($table) {
            $cmd[] = $table; // solo una tabla
        }

        // Usamos ENV MYSQL_PWD para no exponer -p en argumentos
        $env = ['MYSQL_PWD' => $pass];

        $this->info('Ejecutando mysqldump...');
        $process = new Process($cmd, null, $env, null, 600); // timeout 10 min
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->error(trim($buffer));
            } else {
                $this->output->write('.');
            }
        });

        if (!$process->isSuccessful()) {
            $this->newLine();
            $this->error('Fallo mysqldump: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }

        // Guardar el dump en archivo
        file_put_contents($pathAbs, $process->getOutput());
        $this->newLine();
        $this->info("Backup generado: storage/app/{$pathRel}");

        // ¿Comprimir?
        if ($this->option('compress')) {
            $gzPathAbs = $pathAbs . '.gz';
            $this->info('Comprimiendo...');
            $gz = gzopen($gzPathAbs, 'wb9');
            gzwrite($gz, file_get_contents($pathAbs));
            gzclose($gz);
            // Elimina el .sql sin comprimir
            @unlink($pathAbs);
            $pathAbs .= '.gz';
            $pathRel .= '.gz';
            $this->info("Backup comprimido: storage/app/{$pathRel}");
        }

        // Rotación: mantener N archivos más recientes
        $keep = (int)$this->option('keep');
        if ($keep > 0) {
            $this->rotateBackups($disk, $dir, $prefix, $db, $table, $keep);
        }

        $this->info('Backup OK ✅');
        return Command::SUCCESS;
    }

    protected function rotateBackups($disk, string $dir, string $prefix, string $db, ?string $table, int $keep): void
    {
        // Listar archivos de este prefijo/BD (y tabla si aplica)
        $pattern = $table ? "{$prefix}_{$db}_{$table}_" : "{$prefix}_{$db}_";
        $files = collect($disk->files($dir))
            ->filter(fn($f) => str_contains($f, $pattern) && (str_ends_with($f, '.sql') || str_ends_with($f, '.sql.gz')))
            ->sortDesc(); // por nombre incluye timestamp => ordena

        if ($files->count() > $keep) {
            $toDelete = $files->slice($keep);
            foreach ($toDelete as $f) {
                $disk->delete($f);
                $this->line("Rotación: eliminado {$f}");
            }
        }
    }
}
