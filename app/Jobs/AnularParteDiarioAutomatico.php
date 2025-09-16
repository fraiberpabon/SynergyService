<?php

namespace App\Jobs;

use App\Models\ParteDiario\WbParteDiario;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnularParteDiarioAutomatico implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $idEquipo;
    private int $user;
    private int $turno;
    private $fecha;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $idEquipo, int $user, int $turno, $fecha)
    {
        $this->idEquipo = $idEquipo;
        $this->user = $user;
        $this->turno = $turno;
        $this->fecha = $fecha;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $parteDiario = WbParteDiario::where('fk_equiment_id', $this->idEquipo)
                ->where('fecha_registro', $this->fecha)
                ->where('fk_id_seguridad_sitio_turno', $this->turno)
                ->where('fk_matricula_operador', null)
                ->where('fk_id_user_created', 0)
                ->where('estado', 1)
                ->first();

            if (!$parteDiario) return;

            $date = Carbon::now()->toDateTimeString();
            $fechaAnulacion = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y H:i:s');

            $parteDiario->estado = 0; // Anulado
            $parteDiario->fk_id_user_updated = $this->user;
            $parteDiario->fk_usuario_anulacion = $this->user;
            $parteDiario->motivo_anulacion = __('messages.anulado_por_nuevo_parte_diario');
            $parteDiario->fecha_anulacion = $fechaAnulacion;
            $parteDiario->save();
        } catch (\Throwable $th) {
            Log::error('Anular parte diario automatico, Error: ' . $th);
        }
    }
}
