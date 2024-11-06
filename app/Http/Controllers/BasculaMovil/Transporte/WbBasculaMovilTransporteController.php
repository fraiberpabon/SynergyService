<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers\BasculaMovil\Transporte;

use App\Http\interfaces\Vervos;
use App\Models\Transporte\WbBasculaMovilTransporte;
use App\Models\WbSolicitudMateriales;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;

class WbBasculaMovilTransporteController extends BaseController implements Vervos
{

    public function post(Request $req)
    {
    }

    public function postArray(Request $req)
    {
        try {
            $validate = Validator::make($req->all(), [
                'datos' => 'required',
            ]);

            if ($validate->fails()) {
                return $this->handleAlert($validate->errors());
            }

            $respuesta = collect();

            $listaGuardar = json_decode($req->datos, true);

            if (is_array($listaGuardar) && sizeof($listaGuardar) > 0) {
                $guardados = 0;
                foreach ($listaGuardar as $key => $info) {
                    $validacion = Validator::make($info, [
                        'identificador' => 'required|numeric',
                        'boucher' => 'required|string',
                        'esExterno' => 'required|numeric',
                        'tipo' => 'required|numeric',
                        'origen_planta_id' => 'nullable',
                        'origen_tramo_id' => 'nullable',
                        'origen_hito_id' => 'nullable',
                        'origen_otro' => 'nullable',
                        'origen_cost_center_id' => 'nullable',
                        'destino_planta_id' => 'nullable',
                        'destino_tramo_id' => 'nullable',
                        'destino_hito_id' => 'nullable',
                        'destino_otro' => 'nullable',
                        'destino_cost_center_id' => 'nullable',
                        'fk_material_id' => 'nullable',
                        'fk_formula_id' => 'nullable',
                        'fk_equipo_id' => 'required|numeric',
                        'conductor_dni' => 'nullable|numeric',
                        'peso1' => 'nullable',
                        'peso2' => 'nullable',
                        'peso_neto' => 'nullable',
                        'fk_usuario_created_id' => 'required|string',
                        'ubicacion_gps' => 'nullable|string',
                        'fecha' => 'required|string',
                        'observacion' => 'nullable|string',
                        'proyecto' => 'required|string',
                        'hash' => 'required|string'
                    ]);

                    if ($validacion->fails()) {
                        continue;
                    }

                    $find = WbBasculaMovilTransporte::select('id')->where('hash', $info['hash'])->first();
                    if ($find != null) {
                        $guardados++;
                        $itemRespuesta = collect();
                        $itemRespuesta->put('identificador', $info['identificador']);
                        $itemRespuesta->put('estado', '1');
                        $respuesta->push($itemRespuesta);
                        continue;
                    }

                    $model = new WbBasculaMovilTransporte();
                    //$model->id_equipos_horometros_ubicaciones = $info['identificador'];
                    $model->boucher = isset($info['boucher']) ? $info['boucher'] : null;
                    $model->es_externo = isset($info['esExterno']) ? $info['esExterno'] : null;
                    $model->tipo = isset($info['tipo']) ? $info['tipo'] : null;

                    $model->fk_id_planta_origen = isset($info['origen_planta_id']) ? $info['origen_planta_id'] : null;
                    $model->fk_id_tramo_origen = isset($info['origen_tramo_id']) ? $info['origen_tramo_id'] : null;
                    $model->fk_id_hito_origen = isset($info['origen_hito_id']) ? $info['origen_hito_id'] : null;
                    $model->otro_origen = isset($info['origen_otro']) ? $info['origen_otro'] : null;
                    $model->fk_id_cost_center_origen = isset($info['origen_cost_center_id']) ? $info['origen_cost_center_id'] : null;

                    $model->fk_id_planta_destino = isset($info['destino_planta_id']) ? $info['destino_planta_id'] : null;
                    $model->fk_id_tramo_destino = isset($info['destino_tramo_id']) ? $info['destino_tramo_id'] : null;
                    $model->fk_id_hito_destino = isset($info['destino_hito_id']) ? $info['destino_hito_id'] : null;
                    $model->otro_destino = isset($info['destino_otro']) ? $info['destino_otro'] : null;
                    $model->fk_id_cost_center_destino = isset($info['destino_cost_center_id']) ? $info['destino_cost_center_id'] : null;

                    $model->fk_id_material = isset($info['fk_material_id']) ? $info['fk_material_id'] : null;
                    $model->fk_id_formula = isset($info['fk_formula_id']) ? $info['fk_formula_id'] : null;
                    $model->fk_id_equipo = isset($info['fk_equipo_id']) ? $info['fk_equipo_id'] : null;
                    $model->conductor = isset($info['conductor_dni']) ? $info['conductor_dni'] : null;
                    $model->observacion = isset($info['observacion']) ? $info['observacion'] : null;

                    $model->peso1 = isset($info['peso1']) ? $info['peso1'] : null;
                    $model->peso2 = isset($info['peso2']) ? $info['peso2'] : null;
                    $model->peso_neto = isset($info['peso_neto']) ? $info['peso_neto'] : null;

                    $model->fecha_registro = isset($info['fecha']) ? $info['fecha'] : null;
                    $model->estado = 1;

                    $model->fk_id_project_Company = isset($info['proyecto']) ? $info['proyecto'] : null;

                    $model->ubicacion_gps = isset($info['ubicacion_gps']) ? $info['ubicacion_gps'] : null;

                    $model->user_created = isset($info['fk_usuario_created_id']) ? $info['fk_usuario_created_id'] : null;

                    $model->hash = isset($info['hash']) ? $info['hash'] : null;

                    if (!$model->save()) {
                        continue;
                    }

                    $guardados++;
                    $itemRespuesta = collect();
                    $itemRespuesta->put('identificador', $info['identificador']);
                    $itemRespuesta->put('estado', '1');
                    $respuesta->push($itemRespuesta);
                }

                if ($guardados == 0) {
                    return $this->handleAlert("empty");
                }

                return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
            } else {
                return $this->handleAlert("empty");
            }
        } catch (\Throwable $th) {
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
     * Funcion de get no tocar por la interface de vervos
     */
    public function get(Request $request)
    {
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
