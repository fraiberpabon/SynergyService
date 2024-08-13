<?php

namespace App\Console;

use App\Http\Controllers\WbSeguridadSitioController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        /*INICIALIZAR LAS SOLICITUDES DE SEGURIDAD EN SITIO*/
        $schedule->call(function () {
            try {
                $secu = new WbSeguridadSitioController();
                echo $secu->iniciarSolicitudes();
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }

        })
            /* ->timezone('America/Bogota')
            ->everyMinute()
            ->between('08:48', '08:50') */;
        //->between('01:30', '02:00');

        /*FINALIZAR LAS SOLICITUDES DE SEGURIDAD EN SITIO*/
        $schedule->call(function () {
            try {
                $secu = new WbSeguridadSitioController();
                echo $secu->finalizar_anular_solicitudes();
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        })
            /* ->timezone('America/Bogota')
            ->everyMinute()
            ->between('08:52', '08:54') */;
        //->between('23:29', '23:59');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}