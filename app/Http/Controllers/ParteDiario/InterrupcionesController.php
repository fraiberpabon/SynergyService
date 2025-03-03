<?php

namespace App\Http\Controllers\ParteDiario;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Http\interfaces\Vervos;
use App\Models\ParteDiario\Interrupciones;
use App\Models\ParteDiario\WbInterrupciones;
use App\Models\ParteDiario\WbParteDiario;
use App\Models\ParteDiario\WbDistribucionesParteDiario;
use Illuminate\Support\Facades\Validator;
use Exception;
class InterrupcionesController extends BaseController implements Vervos
{


    public function post(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'fecha_registro' => 'required|string',
                'fecha_creacion' => 'required|string',
                'fk_equipo_id' => 'required|string',
                'observacion' => 'nullable',
                'fk_turno'=> 'required|string',
                'horometro_inicial' => 'required|string',
                'horometro_final' => 'required|string',
                'matricula_operador' => 'required|string',
                'hash' => 'required|string',
                'estado' => 'required|string',
                'usuario'=> 'required|string',
                'usuario_actualizacion'=> 'nullable',
                'proyecto' => 'required|string',
            
            ]);
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }
            $id_parte_diario = null;
            $respuesta = collect();
            $respuesta->put('hash', $req->hash);
            $find = WbParteDiario::select('id_parte_diario')->where('hash', $req->hash)->first();
            // if ($find != null) {
            //    // $id_parte_diario = (new WbSolicitudesController())->findForId($find->fk_id_solicitud, $req->tipo ? $req->tipo : null);
            // } else {
                $model = new WbParteDiario();
                $model->fecha_registro = $req->fecha_registro ? $req->fecha_registro : null;
                $model->fecha_creacion_registro = $req->fecha_creacion  ? $req->fecha_creacion : null;
                $model->fk_equiment_id = $req->fk_equipo_id;
                $model->observacion = $req->observacion ? $req->observacion : null;
                $model->fk_id_seguridad_sitio_turno = $req->fk_turno ? $req->fk_turno : null;
                $model->horometro_inicial = $req->horometro_inicial ? $req->horometro_inicial : null;
                $model->horometro_final = $req->horometro_final ? $req->horometro_final : null;
                $model->fk_matricula_operador = $req->matricula_operador ? $req->matricula_operador : null;
                $model->hash = $req->hash ? $req->hash : null;
                $model->fk_id_user_created = $req->usuario ? $req->usuario : null;
                $model->fk_id_user_updated = $req->usuario_actualizacion ? $req->usuario_actualizacion : null;
                $model->fk_id_project_Company = $req->proyecto ? $req->proyecto : null;
                $model->estado = 1;
                if (!$model->save()) {
                    return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
                }
                $this->postInterrupciones($req);
         //   }
        } catch (\Throwable $th) {
            \Log::error('Sy parte diario' . $th->getMessage());
            return $this->handleAlert($th->getMessage());
        }
    }


    public function postInterrupciones(Request $req) {
        try {
                $data = $req->interrupciones;
                if (is_array($data)) {
                    foreach ($data  as $interrupcion) {
                        $model = new WbDistribucionesParteDiario();
                        $model->fk_id_parte_diario = $interrupcion['fk_parte_diario'] ?? null;
                        $model->descripcion_trabajo = $interrupcion['descripcion'] ?? null;
                        $model->fk_id_centro_costo = $interrupcion['fk_centro_costo_id'] ?? null;
                        $model->hr_trabajo = $interrupcion['horas'] ?? null;
                        $model->cant_viajes = $interrupcion['cant_viajes'] ?? null;
                        $model->fk_id_interrupcion = $interrupcion['identificador'] ?? null;
                        $model->fk_id_project_Company = $interrupcion['proyecto'] ?? null;
                        $model->fk_id_user_created = $interrupcion['usuario'] ?? null;
                        $model->hash = $interrupcion['hash'] ?? null;
                        $model->estado = 1;
                        if (!$model->save()) {
                            return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
                        }
                    }

            }
        } catch (\Throwable $th) {
            \Log::error('Sy parte diario' . $th->getMessage());
            return $this->handleAlert($th->getMessage());
        }
    }




  /**
     * Funcion de update no tocar por la interface de vervos
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

      /**
     * Funcion de delete no tocar por la interface de vervos
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

     /**
     * Funcion de get no tocar por la interface de vervos  un comentario
     */
    public function get(Request $request)
    {
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $query = WbInterrupciones::where('estado', 1)
                ->where('fk_id_project_Company', $proyecto);
            $result =$query->get();

            return $this->handleResponse(
                $request,
                $this->WbInterrupcionesToArray($result),
                __('messages.consultado')
            );
        } catch (Exception $e) {
             \Log::error('error get conductores ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
    
}
