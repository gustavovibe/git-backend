<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Aws\S3\S3Client;
use Carbon\Carbon;

class BackupDatabaseToS3 extends Command
{

    protected $signature = 'backup:database-s3';


    protected $description = 'Genera un respaldo de la base de datos y lo almacena en S3';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $this->info('Iniciando respaldo de la base de datos...');

        $filename = 'backup-' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
        $filePath = storage_path('app/' . $filename);

        $command = "mysqldump --no-tablespaces --user=" . env('DB_USERNAME') .
        " --password=" . env('DB_PASSWORD') .
        " --host=" . env('DB_HOST') .
        " --port=" . env('DB_PORT') .
        " " . env('DB_DATABASE') .
        " > " . $filePath;

        $this->info($command);

        $result = null;
        $output = null;
        exec($command, $output, $result);

        if ($result === 0) {
            $this->info('Respaldo generado exitosamente.');

            Storage::disk('s3')->put('daily_backups/' . $filename, file_get_contents($filePath));
            unlink($filePath);

            $this->info('Respaldo subido a S3 exitosamente.');
        } else {
            $this->error('Error al generar el respaldo.');
        }

        return 0;
    }
}
