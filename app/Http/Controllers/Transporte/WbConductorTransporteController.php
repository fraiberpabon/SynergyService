<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers\Transporte;

use App\Http\interfaces\Vervos;
use App\Models\Equipos\WbEquipo;
use App\Models\Transporte\WbTransporteRegistro;
use App\Models\WbSolicitudMateriales;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;

class WbConductorTransporteController extends BaseController implements Vervos
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
                        'numero_vale' => 'required|string',
                        'tipo' => 'required|numeric',
                        'solicitud_id' => 'required|numeric',
                        'origen_planta_id' => 'nullable',
                        'origen_tramo_id' => 'nullable',
                        'origen_hito_id' => 'nullable',
                        'origen_abscisa' => 'nullable',
                        'destino_planta_id' => 'nullable',
                        'destino_tramo_id' => 'nullable',
                        'destino_hito_id' => 'nullable',
                        'destino_abscisa' => 'nullable',
                        'cost_center' => 'required',
                        'material_id' => 'nullable',
                        'formula_id' => 'nullable',
                        'equipo_id' => 'required|numeric',
                        'conductor_dni' => 'nullable|numeric',
                        'cantidad' => 'nullable',
                        'usuario_id' => 'required|string',
                        'ubicacion' => 'nullable|string',
                        'fecha' => 'required|string',
                        'observacion' => 'nullable|string',
                        'proyecto' => 'required|string',
                        'hash' => 'required|string'
                    ]);

                    if ($validacion->fails()) {
                        continue;
                    }

                    $find = WbTransporteRegistro::select('id')->where('hash', $info['hash'])->first();
                    if ($find != null) {
                        $guardados++;
                        $itemRespuesta = collect();
                        $itemRespuesta->put('identificador', $info['identificador']);
                        $itemRespuesta->put('estado', '1');
                        $respuesta->push($itemRespuesta);
                        continue;
                    }

                    $model = new WbTransporteRegistro();
                    //$model->id_equipos_horometros_ubicaciones = $info['identificador'];
                    $model->tipo = isset($info['tipo']) ? $info['tipo'] : null;
                    $model->ticket = isset($info['numero_vale']) ? $info['numero_vale'] : null;
                    $model->fk_id_solicitud = isset($info['solicitud_id']) ? $info['solicitud_id'] : null;
                    $model->fk_id_planta_origen = isset($info['origen_planta_id']) ? $info['origen_planta_id'] : null;
                    $model->fk_id_tramo_origen = isset($info['origen_tramo_id']) ? $info['origen_tramo_id'] : null;
                    $model->fk_id_hito_origen = isset($info['origen_hito_id']) ? $info['origen_hito_id'] : null;
                    $model->abscisa_origen = isset($info['origen_abscisa']) ? $info['origen_abscisa'] : null;
                    $model->fk_id_planta_destino = isset($info['destino_planta_id']) ? $info['destino_planta_id'] : null;
                    $model->fk_id_tramo_destino = isset($info['destino_tramo_id']) ? $info['destino_tramo_id'] : null;
                    $model->fk_id_hito_destino = isset($info['destino_hito_id']) ? $info['destino_hito_id'] : null;
                    $model->abscisa_destino = isset($info['destino_abscisa']) ? $info['destino_abscisa'] : null;
                    $model->fk_id_cost_center = isset($info['cost_center']) ? $info['cost_center'] : null;
                    $model->fk_id_material = isset($info['material_id']) ? $info['material_id'] : null;
                    $model->fk_id_formula = isset($info['formula_id']) ? $info['formula_id'] : null;
                    $model->fk_id_equipo = isset($info['equipo_id']) ? $info['equipo_id'] : null;
                    $model->chofer = isset($info['conductor_dni']) ? $info['conductor_dni'] : null;
                    $model->observacion = isset($info['observacion']) ? $info['observacion'] : null;
                    $model->cantidad = isset($info['cantidad']) ? $info['cantidad'] : null;
                    $model->fecha_registro = isset($info['fecha']) ? $info['fecha'] : null;
                    $model->estado = 1;
                    $model->fk_id_project_Company = isset($info['proyecto']) ? $info['proyecto'] : null;
                    $model->ubicacion_gps = isset($info['ubicacion']) ? $info['ubicacion'] : null;
                    $model->user_created = isset($info['usuario_id']) ? $info['usuario_id'] : null;
                    $model->hash = isset($info['hash']) ? $info['hash'] : null;

                    if (!$model->save()) {
                        continue;
                    }

                    $guardados++;
                    $itemRespuesta = collect();
                    $itemRespuesta->put('identificador', $info['identificador']);
                    $itemRespuesta->put('estado', '1');
                    $respuesta->push($itemRespuesta);

                    $this->actualizarSolicitud($model);
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


    private function actualizarSolicitud(WbTransporteRegistro $item)
    {
        $solicitud = WbSolicitudMateriales::where('id_solicitud_Materiales', $item->fk_id_solicitud)
            ->where('fk_id_project_Company', $item->fk_id_project_Company)
            ->with([
                'transporte' => function ($sub) {
                    $sub->with('equipo')->where('tipo', 2);
                }
            ])
            ->first();

        // Consultamos si encontramos una solicitud
        if (!$solicitud) {
            return;
        }

        // Consultamos que la solicitud no ha sido despachada en su totalidad
        if ($solicitud->fk_id_estados && $solicitud->fk_id_estados == 15) {
            return;
        }

        // Consultamos si la solicitud tiene por lo menos algun transporte registrado
        if (!$solicitud->transporte) {
            return;
        }

        $cubicaje = 0;

        $cubicaje = $solicitud->transporte->filter(fn($tr) => $tr->equipo && $tr->equipo->cubicaje != null)
            ->sum(fn($tr) => $tr->equipo->cubicaje ?? 0);

        $redondear = ceil($cubicaje ?? 0);

        // Convertir el valor de la cantidad fuera del condicional
        $convertCantidad = floatval($solicitud->Cantidad);

        if ($convertCantidad > $redondear) {
            return;
        }

        // Asignar valores y guardar la solicitud solo si pasa la validación
        $solicitud->fecha_cierre = Carbon::now()->format('d/m/Y h:i:s A');
        $solicitud->fk_id_estados = 15;
        $solicitud->fk_id_usuarios_update = $item->user_created;

        $solicitud->save();
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
