<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Transporte\WbTransporteRegistro;

class ViajeInterno implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $transporteInterno;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WbTransporteRegistro $transporte)
    {
        $this->transporteInterno = $transporte;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try{
      switch ($this->transporteInterno['tipo']) {
        case '2':
          $entradas=WbTransporteRegistro::where('tipo',1)->where('codigo_viaje',$this->transporteInterno['codigo_viaje'])->count();
          if ($entradas==0) {
            $model = new WbTransporteRegistro();

            $model->tipo = 1;
            $model->ticket = $this->transporteInterno['ticket'] ? $this->transporteInterno['ticket'] : null;
            $model->fk_id_solicitud = $this->transporteInterno['fk_id_solicitud'] ? $this->transporteInterno['fk_id_solicitud'] : null;
            $model->fk_id_planta_origen = $this->transporteInterno['fk_id_planta_origen'] ? $this->transporteInterno['fk_id_planta_origen'] : null;

            $model->fk_id_tramo_origen = $this->transporteInterno['fk_id_tramo_origen'] ? $this->transporteInterno['fk_id_tramo_origen'] : null;
            $model->id_tramo_origen = $this->transporteInterno['id_tramo_origen'] ? $this->transporteInterno['id_tramo_origen'] : null;

            $model->fk_id_hito_origen = $this->transporteInterno['fk_id_hito_origen'] ? $this->transporteInterno['fk_id_hito_origen'] : null;
            $model->id_hito_origen = $this->transporteInterno['id_hito_origen'] ? $this->transporteInterno['id_hito_origen'] : null;

            $model->abscisa_origen = $this->transporteInterno['abscisa_origen'] ? $this->transporteInterno['abscisa_origen'] : null;

            $model->fk_id_planta_destino = $this->transporteInterno['fk_id_planta_destino'] ? $this->transporteInterno['fk_id_planta_destino'] : null;

            $model->fk_id_tramo_destino = $this->transporteInterno['fk_id_tramo_destino'] ? $this->transporteInterno['fk_id_tramo_destino'] : null;
            $model->id_tramo_destino = $this->transporteInterno['id_tramo_destino'] ? $this->transporteInterno['id_tramo_destino'] : null;

            $model->fk_id_hito_destino = $this->transporteInterno['fk_id_hito_destino'] ? $this->transporteInterno['fk_id_hito_destino'] : null;
            $model->id_hito_destino = $this->transporteInterno['id_hito_destino'] ? $this->transporteInterno['id_hito_destino'] : null;

            $model->abscisa_destino = $this->transporteInterno['abscisa_destino'] ? $this->transporteInterno['abscisa_destino'] : null;

            $model->fk_id_cost_center = $this->transporteInterno['fk_id_cost_center'] ? $this->transporteInterno['fk_id_cost_center'] : null;
            $model->fk_id_material = $this->transporteInterno['fk_id_material'] ? $this->transporteInterno['fk_id_material'] : null;
            $model->fk_id_formula = $this->transporteInterno['fk_id_formula'] ? $this->transporteInterno['fk_id_formula'] : null;
            $model->fk_id_equipo = $this->transporteInterno['fk_id_equipo'] ? $this->transporteInterno['fk_id_equipo'] : null;
            $model->chofer = $this->transporteInterno['chofer'] ? $this->transporteInterno['chofer'] : null;
            $model->observacion = 'Generado automaticamente';
            $model->cantidad = $this->transporteInterno['cantidad'] ? $this->transporteInterno['cantidad'] : null;
            $model->fecha_registro = $this->transporteInterno['fecha_registro'] ? $this->transporteInterno['fecha_registro'] : null;
            $model->estado = 1;
            $model->fk_id_project_Company = $this->transporteInterno['fk_id_project_Company'] ? $this->transporteInterno['fk_id_project_Company'] : null;
            $model->ubicacion_gps = $this->transporteInterno['ubicacion_gps'] ? $this->transporteInterno['ubicacion_gps'] : null;
            $model->user_created = 0;
            $model->hash = $this->transporteInterno['hash'] ? $this->transporteInterno['hash'] : null;
            $model->codigo_viaje = $this->transporteInterno['codigo_viaje'] ? $this->transporteInterno['codigo_viaje'] : null;

            $model->cubicaje =  $this->transporteInterno['cubicaje'] ? $this->transporteInterno['cubicaje'] : null;
            $model->save();
          }
          break;

        default:
          $entradas=WbTransporteRegistro::where('tipo',1)->where('codigo_viaje',$this->transporteInterno['codigo_viaje'])->where('user_created',0)->where('observacion','Generado automaticamente')->where('estado',1)->update(['estado' => 0,'user_updated'=>0,'observacion_cierre'=>'Anulado por registro manual']);
          break;
      }
    } catch (\Throwable $th) {
        \Log::error('Transporte Interno, Error: '. $th);
    }

    }
}
