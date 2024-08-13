<?php

namespace App\Console\Commands;

use App\Http\Controllers\WbTareasProgramadasController;
use Illuminate\Console\Command;

class ejecutarTareasProgramadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webu_programador_tareas:ejecutar_tareas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta todas las tareas que esten programadas para ejecucion el dia de hoy.';

    /**
     * Ejecuta el comando de consola para realizar tareas programadas.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Invoca el método que ejecuta las tareas programadas
            (new WbTareasProgramadasController())->ejecutarTareasProgramadas();
        } catch (\Throwable $e) {
            // Captura cualquier excepción y la muestra en la consola
            // También se puede registrar el error en el log descomentando la línea siguiente
            //\Log::error($e->getMessage());
            $this->error($e->getMessage());
        }

        // Retorna el código de salida (éxito en este caso)
        return 0;
    }

}