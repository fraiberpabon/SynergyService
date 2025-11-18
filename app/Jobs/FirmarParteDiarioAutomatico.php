<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\Equipos\WbEquipo;
use Illuminate\Support\Facades\Log;
use App\Models\WbConfiguraciones;
use App\Models\Modulos\WbResponsablesArea;
use App\Models\ParteDiario\WbParteDiario;
class FirmarParteDiarioAutomatico implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private int $idEquipo;
    private int $idParteDiario;

    private int $proyecto;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $idEquipo, int $idParteDiario,int $proyecto)
    {
        $this->idEquipo = $idEquipo;
        $this->idParteDiario = $idParteDiario;
        $this->proyecto = $proyecto;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

        $configuracionFirma = WbConfiguraciones::where('fk_id_project_Company',  $this->proyecto)
            ->select('firma_parte_diario')
            ->first();

        if (!$configuracionFirma || $configuracionFirma->firma_parte_diario != '1') {
            Log::info("Firma no válida para el proyecto { $this->proyecto}: el firmante no está habilitado para firmar el parte diario {$this->idParteDiario}");
            return; // ⛔️ Detiene la firma automática
        }

            $equipos = WbEquipo::where('id', $this->idEquipo)->first();
            $AreaEquipo = $equipos->fk_id_area;
            if (!$equipos)
                return;
            $buscarFirma = WbResponsablesArea::where('fk_id_area', $AreaEquipo)
                ->where('estado', 1)
                ->where('fk_id_modulo', '=', '1')
                ->where('firma', '=', '1')
                ->first();
            if ($buscarFirma) {
                $usuarioFirma = $buscarFirma->fk_id_usuario;
            } else {
                Log::error("No se encontró responsable con firma para el área: {$AreaEquipo}");
                return;
            }
            $parteDiario = WbParteDiario::where('id_parte_diario', $this->idParteDiario)
                ->where('estado', 1)
                ->first();
            if (!$parteDiario)
                return;
            $date = Carbon::now()->toDateTimeString();
            $fechaFirmaParteDiario = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y H:i:s');
            $parteDiario->usuario_firma = $usuarioFirma;
            $parteDiario->fecha_firma = $fechaFirmaParteDiario;
            $parteDiario->firma_automatica = 1;
            $parteDiario->save();
        } catch (\Throwable $th) {
            Log::error('Anular parte diario automatico, Error: ' . $th);
        }
    }
}
