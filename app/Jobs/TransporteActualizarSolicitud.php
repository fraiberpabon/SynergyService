<?php

namespace App\Jobs;

use App\Http\Controllers\Transporte\WbTransporteRegistroController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Transporte\WbTransporteRegistro;

class TransporteActualizarSolicitud implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $newTransport;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WbTransporteRegistro $transporte)
    {
        $this->newTransport = $transporte;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            (new WbTransporteRegistroController())->actualizarSolicitudV2($this->newTransport);
        } catch (\Throwable $th) {
            \Log::error('Transporte actualizar solicitud, Error: ' . $th);
        }

    }
}
