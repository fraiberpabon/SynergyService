<?php

namespace App\Console\Commands;

use App\Http\Controllers\WbSeguridadSitioController;
use Illuminate\Console\Command;

class inicializarSolicitudes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seguridad_sitio:inicializar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicializa las solicitudes que esten aprobadas';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $secu = new WbSeguridadSitioController();
            $secu->iniciarSolicitudes();
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
        return 0;
    }
}