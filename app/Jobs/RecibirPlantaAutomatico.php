<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Transporte\WbTransporteRegistro;
use App\Models\UsuPlanta;

class RecibirPlantaAutomatico implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $transportePlanta;
    /**
     * Create a new job instance.
     *
     * @return void
     */
     public function __construct(WbTransporteRegistro $transporte)
     {
         $this->transportePlanta = $transporte;
     }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try{
        $planta=UsuPlanta::find($this->transportePlanta['fk_id_planta_destino']);
        if($planta && $planta->recibirAutomatico==1){
          switch ($this->transportePlanta['tipo']) {
            case '2':
              $entradas=WbTransporteRegistro::where('tipo',1)->where('codigo_viaje',$this->transportePlanta['codigo_viaje'])->count();
              if ($entradas==0) {
                $model = new WbTransporteRegistro();

                $model->tipo = 1;
                $model->ticket = $this->transportePlanta['ticket'] ? $this->transportePlanta['ticket'] : null;
                $model->fk_id_solicitud = $this->transportePlanta['fk_id_solicitud'] ? $this->transportePlanta['fk_id_solicitud'] : null;
                $model->fk_id_planta_origen = $this->transportePlanta['fk_id_planta_origen'] ? $this->transportePlanta['fk_id_planta_origen'] : null;

                $model->fk_id_tramo_origen = $this->transportePlanta['fk_id_tramo_origen'] ? $this->transportePlanta['fk_id_tramo_origen'] : null;
                $model->id_tramo_origen = $this->transportePlanta['id_tramo_origen'] ? $this->transportePlanta['id_tramo_origen'] : null;

                $model->fk_id_hito_origen = $this->transportePlanta['fk_id_hito_origen'] ? $this->transportePlanta['fk_id_hito_origen'] : null;
                $model->id_hito_origen = $this->transportePlanta['id_hito_origen'] ? $this->transportePlanta['id_hito_origen'] : null;

                $model->abscisa_origen = $this->transportePlanta['abscisa_origen'] ? $this->transportePlanta['abscisa_origen'] : null;

                $model->fk_id_planta_destino = $this->transportePlanta['fk_id_planta_destino'] ? $this->transportePlanta['fk_id_planta_destino'] : null;

                $model->fk_id_tramo_destino = $this->transportePlanta['fk_id_tramo_destino'] ? $this->transportePlanta['fk_id_tramo_destino'] : null;
                $model->id_tramo_destino = $this->transportePlanta['id_tramo_destino'] ? $this->transportePlanta['id_tramo_destino'] : null;

                $model->fk_id_hito_destino = $this->transportePlanta['fk_id_hito_destino'] ? $this->transportePlanta['fk_id_hito_destino'] : null;
                $model->id_hito_destino = $this->transportePlanta['id_hito_destino'] ? $this->transportePlanta['id_hito_destino'] : null;

                $model->abscisa_destino = $this->transportePlanta['abscisa_destino'] ? $this->transportePlanta['abscisa_destino'] : null;

                $model->fk_id_cost_center = $this->transportePlanta['fk_id_cost_center'] ? $this->transportePlanta['fk_id_cost_center'] : null;
                $model->fk_id_material = $this->transportePlanta['fk_id_material'] ? $this->transportePlanta['fk_id_material'] : null;
                $model->fk_id_formula = $this->transportePlanta['fk_id_formula'] ? $this->transportePlanta['fk_id_formula'] : null;
                $model->fk_id_equipo = $this->transportePlanta['fk_id_equipo'] ? $this->transportePlanta['fk_id_equipo'] : null;
                $model->chofer = $this->transportePlanta['chofer'] ? $this->transportePlanta['chofer'] : null;
                $model->observacion = 'Generado automaticamente';
                $model->cantidad = $this->transportePlanta['cantidad'] ? $this->transportePlanta['cantidad'] : null;
                $model->fecha_registro = $this->transportePlanta['fecha_registro'] ? $this->transportePlanta['fecha_registro'] : null;
                $model->estado = 1;
                $model->fk_id_project_Company = $this->transportePlanta['fk_id_project_Company'] ? $this->transportePlanta['fk_id_project_Company'] : null;
                $model->ubicacion_gps = $this->transportePlanta['ubicacion_gps'] ? $this->transportePlanta['ubicacion_gps'] : null;
                $model->user_created = 0;
                $model->hash = $this->transportePlanta['hash'] ? $this->transportePlanta['hash'] : null;
                $model->codigo_viaje = $this->transportePlanta['codigo_viaje'] ? $this->transportePlanta['codigo_viaje'] : null;
                $model->tipo_solicitud = $this->transportePlanta['tipo_solicitud'] ? $this->transportePlanta['tipo_solicitud'] : null;
                $model->code_bascula = $this->transportePlanta['code_bascula'] ? $this->transportePlanta['code_bascula'] : null;
                $model->turno = $this->transportePlanta['turno'] ?? null;
                $model->temperatura = $this->transportePlanta['temperatura'] ? $this->transportePlanta['temperatura'] : null;
                $model->cubicaje =  $this->transportePlanta['cubicaje'] ? $this->transportePlanta['cubicaje'] : null;
                $model->save();
              }
              break;

            default:
              $entradas=WbTransporteRegistro::where('tipo',1)->where('codigo_viaje',$this->transportePlanta['codigo_viaje'])->where('fk_id_solicitud', $this->transportePlanta['fk_id_solicitud'])->where('user_created',0)->where('observacion','Generado automaticamente')->where('estado',1)->update(['estado' => 0,'user_updated'=>0,'observacion_cierre'=>'Anulado por registro manual']);
              break;
          }
        }
    } catch (\Throwable $th) {
        \Log::error('Transporte Interno, Error: '. $th);
    }
    }
}
