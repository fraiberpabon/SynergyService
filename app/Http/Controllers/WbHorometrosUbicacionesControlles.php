<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Http\Resources\EquipementsCollection;
use App\Http\Resources\Wb_horometros_ubicaciones_resource;
use App\Models\Compania;
use App\Models\Equipos\WbEquipoHorometrosUbicaciones;
use App\Models\SyncRelacionVehiculoPesos;
use App\Models\ts_Equipement;
use App\Models\Equipos\WbEquipo;
use App\Models\Equipos\wbTipoEquipo;
use App\Models\WbCompanieProyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class WbHorometrosUbicacionesControlles extends BaseController implements Vervos
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

            if ($validate->fails()){
                return $this->handleAlert($validate->errors());
            }

            $respuesta = collect();

            $listaGuardar = json_decode($req->datos, true);

            if (is_array($listaGuardar) && sizeof($listaGuardar) > 0) {
                foreach ($listaGuardar as $key => $info) {
                    $validacion = Validator::make($info, [
                        'identificador' => 'required|numeric',
                        'equipo_id' => 'required|numeric',
                        'horometro' => 'nullable|string',
                        'horometro_foto' => 'nullable|string',
                        'tramo_id' => 'required|string',
                        'hito_id' => 'required|string',
                        'ubicacion_gps' => 'required|string',
                        'fecha_creacion' => 'required|string',
                        'observacion' => 'nullable|string',
                        'proyecto' => 'required|string',
                        'hash' => 'required|string'
                    ]);

                    if ($validacion->fails()) {
                        continue;
                    }

                    $find = WbEquipoHorometrosUbicaciones::select('id_equipos_horometros_ubicaciones')->where('hash', $info['hash'])->first();
                    if ($find != null) {
                        $itemRespuesta = collect();
                        $itemRespuesta->put('identificador', $info['identificador']);
                        $itemRespuesta->put('estado', '1');
                        $respuesta->push($itemRespuesta);
                        continue;
                    }

                    $model = new WbEquipoHorometrosUbicaciones();
                    //$model->id_equipos_horometros_ubicaciones = $info['identificador'];
                    $model->fk_id_equipo = $info['equipo_id'];
                    $model->fk_id_tramo = $info['tramo_id'];
                    $model->fk_id_hito = $info['hito_id'];
                    $model->horometro = $info['horometro'];
                    $model->horometro_foto = $info['horometro_foto'];
                    $model->observaciones = $info['observacion'];
                    $model->fecha_registro = $info['fecha_creacion'];
                    $model->estado = 0;
                    $model->fk_id_project_Company = $info['proyecto'];
                    $model->ubicacion_gps = $info['ubicacion_gps'];
                    $model->hash = $info['hash'];

                    if (!$model->save()) continue;

                    $itemRespuesta = collect();
                    $itemRespuesta->put('identificador', $info['identificador']);
                    $itemRespuesta->put('estado', '1');
                    $respuesta->push($itemRespuesta);
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
