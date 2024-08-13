<?php

namespace App\Console\Commands;

use App\Http\Controllers\WbSeguridadSitioController;
use Illuminate\Console\Command;

class finalizarSolicitudes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seguridad_sitio:finalizar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finaliza las solicitudes que esten en proceso';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $secu = new WbSeguridadSitioController();
            $secu->finalizar_anular_solicitudes();
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
        return 0;
    }
}