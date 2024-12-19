<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers\Transporte;

use App\Http\interfaces\Vervos;
use App\Models\Transporte\WbConductores;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use Log;

class WbConductoresController extends BaseController implements Vervos
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
                $usuario = $this->traitGetIdUsuarioToken($req);
                $guardados = 0;
                foreach ($listaGuardar as $key => $info) {
                    $validacion = Validator::make($info, [
                        'dni' => 'required|string',
                        'nombre' => 'required|string',
                        'proyecto' => 'required|string',
                    ]);

                    if ($validacion->fails()) {
                        continue;
                    }

                    $find = WbConductores::select('dni')->where('dni', $info['dni'])->first();
                    if ($find != null) {
                        $guardados++;
                        $itemRespuesta = collect();
                        $itemRespuesta->put('dni', $info['dni']);
                        $itemRespuesta->put('estadoSync', '1');
                        $respuesta->push($itemRespuesta);
                        continue;
                    }

                    $model = new WbConductores();
                    //$model->id_equipos_horometros_ubicaciones = $info['identificador'];
                    $model->dni = isset($info['dni']) ? $info['dni'] : null;
                    $model->nombreCompleto = isset($info['nombre']) ? $info['nombre'] : null;
                    $model->estado = 1;
                    $model->fk_user_creador = $usuario;
                    $model->fk_id_project_Company = isset($info['proyecto']) ? $info['proyecto'] : null;

                    if (!$model->save()) {
                        continue;
                    }

                    $guardados++;
                    $itemRespuesta = collect();
                    $itemRespuesta->put('dni', $info['dni']);
                    $itemRespuesta->put('estadoSync', '1');
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
        try {
            $query = WbConductores::where('estado', 1);
            $query = $this->filtrar($request, $query)
                ->get();

            return $this->handleResponse($request, $this->WbConductorestoArray($query), __('messages.consultado'));
        } catch (Exception $e) {
            Log::error('error get conductores ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
