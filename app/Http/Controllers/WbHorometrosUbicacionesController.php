<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Equipos\WbEquipoHorometrosUbicaciones;
use App\Http\Controllers\WbEquipoControlles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class WbHorometrosUbicacionesController extends BaseController implements Vervos
{

    public function post(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric',
                'equipo_id' => 'required|numeric',
                'horometro' => 'nullable|string',
                'horometro_foto' => 'nullable|string',
                'tramo_id' => 'required|string',
                'hito_id' => 'required|string',
                'ubicacion_gps' => 'nullable|string',
                'fecha_creacion' => 'required|string',
                'observacion' => 'nullable|string',
                'proyecto' => 'required|string',
                'hash' => 'required|string',
                'equipo_estado_id' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $equipo = null;
            $response = collect();
            $response->put('hash', $req->hash);

            $find = WbEquipoHorometrosUbicaciones::select('id_equipos_horometros_ubicaciones')->where('hash', $req->hash)->first();
            if ($find) {
                $response->put('estado', '1');
                $equipo = (new WbEquipoControlles())->findForId($find->fk_id_equipo, $find->fk_id_project_Company);
            } else {
                $model = new WbEquipoHorometrosUbicaciones();

                $model->fk_id_equipo = $req->equipo_id ? $req->equipo_id : null;
                $model->fk_id_tramo = $req->tramo_id ? $req->tramo_id : null;
                $model->fk_id_hito = $req->hito_id ? $req->hito_id : null;
                $model->horometro = $req->horometro ? $req->horometro : null;
                $model->horometro_foto = $req->horometro_foto ? $req->horometro_foto : null;
                $model->observaciones = $req->observacion ? $req->observacion : null;
                $model->fecha_registro = $req->fecha_creacion ? $req->fecha_creacion : null;
                $model->estado = 0;
                $model->fk_id_project_Company = $req->proyecto ? $req->proyecto : null;
                $model->ubicacion_gps = $req->ubicacion_gps ? $req->ubicacion_gps : null;
                $model->user_created = $req->usuario ? $req->usuario : null;
                $model->hash = $req->hash ? $req->hash : null;
                $model->fk_id_equipo_estado = $req->equipo_estado_id ? $req->equipo_estado_id : null;

                if (!$model->save()) {
                    return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
                }

                $response->put('estado', '1');

                $equipo = (new WbEquipoControlles())->findForId($model->fk_id_equipo, $model->fk_id_project_Company);
            }

            if ($equipo != null) {
                $response->put('horometro', $equipo['horometro']);
                $response->put('fechaHorometro', $equipo['fechaHorometro']);
                $response->put('ubicacionTramo', $equipo['ubicacionTramo']);
                $response->put('ubicacionHito', $equipo['ubicacionHito']);
                $response->put('fechaUbicacion', $equipo['fechaUbicacion']);
            }

            return $this->handleResponse($req, $response, __('messages.registro_exitoso'));
        } catch (\Throwable $th) {
            \Log::error('error post horometro y ubicaciones -> ' . $th->getMessage());
            return $this->handleAlert(__('messages.error_interno'));
        }
    }

    public function postArray(Request $req)
    {
        $usuario = $this->traitGetIdUsuarioToken($req);
        $general = $req->all();
        try {
            $validate = Validator::make($req->all(), [
                'datos' => 'required',
            ]);

            if ($validate->fails()){
                return $this->handleAlert($validate->errors());
            }

            $respuesta = collect();

            $listaGuardar = json_decode($req->datos, true);

            if (is_array($listaGuardar) && sizeof($listaGuardar) > 0) {
                $guardados = 0;
                foreach ($listaGuardar as $key => $info) {
                    $validacion = Validator::make($info, [
                        'identificador' => 'required|numeric',
                        'equipo_id' => 'required|numeric',
                        'horometro' => 'nullable|string',
                        'horometro_foto' => 'nullable|string',
                        'tramo_id' => 'required|string',
                        'hito_id' => 'required|string',
                        'ubicacion_gps' => 'nullable|string',
                        'fecha_creacion' => 'required|string',
                        'observacion' => 'nullable|string',
                        'proyecto' => 'required|string',
                        'hash' => 'required|string',
                        'equipo_estado_id' => 'nullable|string'
                    ]);

                    if ($validacion->fails()) {
                        continue;
                    }

                    $find = WbEquipoHorometrosUbicaciones::select('id_equipos_horometros_ubicaciones')->where('hash', $info['hash'])->first();
                    if ($find != null) {
                        $guardados++;
                        $itemRespuesta = collect();
                        $itemRespuesta->put('identificador', $info['identificador']);
                        $itemRespuesta->put('estado', '1');
                        $respuesta->push($itemRespuesta);
                        continue;
                    }

                    $model = new WbEquipoHorometrosUbicaciones();
                    //$model->id_equipos_horometros_ubicaciones = $info['identificador'];
                    $model->fk_id_equipo = isset($info['equipo_id']) ? $info['equipo_id'] : null;
                    $model->fk_id_tramo = isset($info['tramo_id']) ? $info['tramo_id'] : null;
                    $model->fk_id_hito = isset($info['hito_id']) ? $info['hito_id'] : null;
                    $model->horometro = isset($info['horometro']) ? $info['horometro'] : null;
                    $model->horometro_foto = isset($info['horometro_foto']) ? $info['horometro_foto'] : null;
                    $model->observaciones = isset($info['observacion']) ? $info['observacion'] : null;
                    $model->fecha_registro = isset($info['fecha_creacion']) ? $info['fecha_creacion'] : null;
                    $model->estado = 0;
                    $model->fk_id_project_Company = isset($info['proyecto']) ? $info['proyecto'] : null;
                    $model->ubicacion_gps = isset($info['ubicacion_gps']) ? $info['ubicacion_gps'] : null;
                    $model->user_created = isset($info['usuario']) ? $info['usuario'] : null;
                    $model->hash = isset($info['hash']) ? $info['hash'] : null;
                    $model->fk_id_equipo_estado = isset($info['equipo_estado_id']) ? $info['equipo_estado_id'] : null;

                    if (!$model->save()) {
                        \Log::error('sync_array_horometers ' . ' Usuario:'.$usuario . ' Error: ' . $info);
                        continue;
                    }

                    $guardados++;
                    $itemRespuesta = collect();
                    $itemRespuesta->put('identificador', $info['identificador']);
                    $itemRespuesta->put('estado', '1');
                    $respuesta->push($itemRespuesta);
                }
                if ($guardados == 0) return $this->handleAlert("empty");
                return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
            } else {
                return $this->handleAlert("empty");
            }
        } catch (\Throwable $th) {
            \Log::error('sync_array_horometers ' . ' Usuario:'.$usuario . ' info: ' . $general. ' Error: ' . $th->getMessage());
            \Log::error($th->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'));
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
