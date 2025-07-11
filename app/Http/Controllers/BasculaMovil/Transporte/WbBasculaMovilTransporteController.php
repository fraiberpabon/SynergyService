<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers\BasculaMovil\Transporte;

use App\Http\interfaces\Vervos;
use App\Models\BasculaMovil\WbBasculaMovilTransporte;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use Log;

class WbBasculaMovilTransporteController extends BaseController implements Vervos
{

    public function post(Request $req)
    {
        try {
            $respuesta = collect();
            $respuesta->put('hash', $req->hash);

            $action = $this->postAction($req->all());
            if (!$action) {
                Log::info('bascula-movil-single-insert error insertar -> ' . $req->hash);
                return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
            }

            return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
        } catch (\Throwable $th) {
            \Log::error('bascula-movil-single-insert ' . $th->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
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
                    $action = $this->postAction($info);
                    if ($action) {
                        $guardados++;
                        $itemRespuesta = collect();
                        $itemRespuesta->put('identificador', $info['identificador']);
                        $itemRespuesta->put('estado', '1');
                        $respuesta->push($itemRespuesta);
                    }
                }

                if ($guardados == 0) {
                    return $this->handleAlert("empty item");
                }

                return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
            } else {
                return $this->handleAlert("empty array");
            }
        } catch (\Throwable $th) {
            \Log::error('bascula-movil-array-insert ' . $th->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }

    private function postAction($info)
    {
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
            'fk_equipo_id' => 'nullable|numeric',
            'conductor_dni' => 'nullable|string',
            'peso1' => 'nullable',
            'peso2' => 'nullable',
            'peso_neto' => 'nullable',
            'fk_usuario_created_id' => 'required|string',
            'ubicacion_gps' => 'nullable|string',
            'fecha' => 'required|string',
            'fechaPeso2' => 'required|string',
            'observacion' => 'nullable|string',
            'proyecto' => 'required|string',
            'hash' => 'required|string',
            'transport_code' => 'nullable|string',
            'tipo_formula' => 'nullable|string',
            'equipo_ext' => 'nullable|string',
            'material_ext' => 'nullable|string',
            'fk_volco_id' => 'nullable|numeric', // Nuevo campo para el ID del volco
            'volco_peso' => 'nullable', // Nuevo campo para el peso del volco
        ]);

        if ($validacion->fails()) {
            \Log::error('bascula-movil-post-action ' . $validacion->errors());
            return false;
        }

        $find = WbBasculaMovilTransporte::select('id')->where('hash', $info['hash'])->first();
        if ($find != null) {
            return true; // Si ya existe, no es necesario insertar de nuevo
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

        $model->peso1 = isset($info['peso1']) ? $info['peso1'] + (isset($info['volco_peso']) ? $info['volco_peso'] : 0) : null;
        $model->peso2 = isset($info['peso2']) ? $info['peso2'] : null;
        $model->peso_neto = isset($info['peso_neto']) ? $info['peso_neto'] : null;

        $model->fecha_registro = isset($info['fecha']) ? $info['fecha'] : null;
        $model->fecha_registro_peso2 = isset($info['fechaPeso2']) ? $info['fechaPeso2'] : null;
        $model->estado = 1;

        $model->fk_id_project_Company = isset($info['proyecto']) ? $info['proyecto'] : null;

        $model->ubicacion_gps = isset($info['ubicacion_gps']) ? $info['ubicacion_gps'] : null;

        $model->user_created = isset($info['fk_usuario_created_id']) ? $info['fk_usuario_created_id'] : null;

        $model->hash = isset($info['hash']) ? $info['hash'] : null;

        $model->codigo_transporte = isset($info['transport_code']) ? $info['transport_code'] : null;

        $model->tipo_formula = isset($info['tipo_formula']) ? $info['tipo_formula'] : null;

        $model->equipo_externo = isset($info['equipo_ext']) ? $info['equipo_ext'] : null;
        $model->material_externo = isset($info['material_ext']) ? $info['material_ext'] : null;

        $model->fk_id_volco = isset($info['fk_volco_id']) ? $info['fk_volco_id'] : null;

        if (!$model->save()) {
            \Log::error('bascula-movil-post-action ' . $model->getErrors());
            return false;
        }
        return true;
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
    public function get(Request $request) {}

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }


    public function GetBasculas(Request $request)
    {
        try {
            $consulta = WbBasculaMovilTransporte::with([
                'origenPlanta',
                'origenTramo',
                'origenHito',
                'destinoPlanta',
                'destinoTramo',
                'destinoHito',
                'cdcOrigen',
                'cdcDestino',
                'material',
                'formula',
                'usuario_creador',
                'usuario_actualizador',
                'equipo',
                'conductores'
            ])->get();
            //var_dump($consulta);
            return $this->handleResponse($request, $this->BasculasToArray($consulta), 'Consultado.');
        } catch (\Exception $e) {
            \Log::error('bascula-movil-getBascula ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }
}
