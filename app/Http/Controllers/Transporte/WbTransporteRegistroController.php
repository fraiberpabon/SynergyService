<?php

/**
 * Aqui se realizan todas las importaciones para usar el controlador
 */

namespace App\Http\Controllers\Transporte;

use App\Http\Controllers\WbSolicitudesController;
use App\Http\interfaces\Vervos;
use App\Jobs\TransporteActualizarSolicitud;
use App\Models\Equipos\WbEquipo;
use App\Models\PlanillaControlAsfalto;
use App\Models\WbConfiguraciones;
use App\Models\Transporte\WbTransporteRegistro;
use App\Models\WbSolicitudMateriales;
use App\Models\WbSolitudAsfalto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\solicitudMaterialesResource;
use App\Http\Resources\transporteRegistroResource;

use App\Jobs\ViajeInterno;

class WbTransporteRegistroController extends BaseController implements Vervos
{


    /**
     * Funcion que guarda un transporte y envia un mensaje al solicitante
     * que dicha en dicha solicitud, se envio con una cantidad especificada
     * por el usuario de un material especificado en la solicitud
     */
    public function post(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric',
                'numero_vale' => 'required|string',
                'tipo' => 'required|numeric',
                'solicitud_id' => 'required|numeric',
                'origen_planta_id' => 'nullable',
                'origen_tramo_id' => 'nullable',
                'origen_tramo_fk_id' => 'nullable',
                'origen_hito_id' => 'nullable',
                'origen_hito_fk_id' => 'nullable',
                'origen_abscisa' => 'nullable',
                'destino_planta_id' => 'nullable',
                'destino_tramo_id' => 'nullable',
                'destino_tramo_fk_id' => 'nullable',
                'destino_hito_id' => 'nullable',
                'destino_hito_fk_id' => 'nullable',
                'destino_abscisa' => 'nullable',
                'cost_center' => 'required',
                'material_id' => 'nullable',
                'formula_id' => 'nullable',
                'equipo_id' => 'required|numeric',
                'equipo_cubicaje' => 'nullable',
                'conductor_dni' => 'nullable|numeric',
                'cantidad' => 'nullable',
                'usuario_id' => 'required|string',
                'ubicacion' => 'nullable|string',
                'fecha' => 'required|string',
                'observacion' => 'nullable|string',
                'proyecto' => 'required|string',
                'hash' => 'required|string',
                'unique_code' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $solicitud = null;
            $respuesta = collect();
            $respuesta->put('hash', $req->hash);

            $find = WbTransporteRegistro::select('id', 'fk_id_solicitud')->where('hash', $req->hash)->first();
            if ($find != null) {
                $solicitud = (new WbSolicitudesController())->findForId($find->fk_id_solicitud, $req->tipo ? $req->tipo : null);
            } else {
                $model = new WbTransporteRegistro();

                $model->tipo = $req->tipo ? $req->tipo : null;
                $model->ticket = $req->numero_vale ? $req->numero_vale : null;
                $model->fk_id_solicitud = $req->solicitud_id ? $req->solicitud_id : null;
                $model->fk_id_planta_origen = $req->origen_planta_id ? $req->origen_planta_id : null;

                $model->fk_id_tramo_origen = $req->origen_tramo_id ? $req->origen_tramo_id : null;
                $model->id_tramo_origen = $req->origen_tramo_fk_id ? $req->origen_tramo_fk_id : null;

                $model->fk_id_hito_origen = $req->origen_hito_id ? $req->origen_hito_id : null;
                $model->id_hito_origen = $req->origen_hito_fk_id ? $req->origen_hito_fk_id : null;

                $model->abscisa_origen = $req->origen_abscisa ? $req->origen_abscisa : null;

                $model->fk_id_planta_destino = $req->destino_planta_id ? $req->destino_planta_id : null;

                $model->fk_id_tramo_destino = $req->destino_tramo_id ? $req->destino_tramo_id : null;
                $model->id_tramo_destino = $req->destino_tramo_fk_id ? $req->destino_tramo_fk_id : null;

                $model->fk_id_hito_destino = $req->destino_hito_id ? $req->destino_hito_id : null;
                $model->id_hito_destino = $req->destino_hito_fk_id ? $req->destino_hito_fk_id : null;

                $model->abscisa_destino = $req->destino_abscisa ? $req->destino_abscisa : null;

                $model->fk_id_cost_center = $req->cost_center ? $req->cost_center : null;
                $model->fk_id_material = $req->material_id ? $req->material_id : null;
                $model->fk_id_formula = $req->formula_id ? $req->formula_id : null;
                $model->fk_id_equipo = $req->equipo_id ? $req->equipo_id : null;
                $model->chofer = $req->conductor_dni ? $req->conductor_dni : null;
                $model->observacion = $req->observacion ? $req->observacion : null;
                $model->cantidad = $req->cantidad ? $req->cantidad : null;
                $model->fecha_registro = $req->fecha ? $req->fecha : null;
                $model->estado = 1;
                $model->fk_id_project_Company = $req->proyecto ? $req->proyecto : null;
                $model->ubicacion_gps = $req->ubicacion ? $req->ubicacion : null;
                $model->user_created = $req->usuario_id ? $req->usuario_id : null;
                $model->hash = $req->hash ? $req->hash : null;
                $model->codigo_viaje = $req->unique_code ? $req->unique_code : null;

                if ($req->equipo_cubicaje) {
                    $model->cubicaje = $req->equipo_cubicaje ? $req->equipo_cubicaje : null;
                } else {
                    $equi = WbEquipo::find($model->fk_id_equipo);
                    if ($equi) {
                        $model->cubicaje = $equi->cubicaje ? $equi->cubicaje : null;
                    }
                }

                if (!$model->save()) {
                    return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
                }

                $this->actualizarSolicitud($model);

                $solicitud = (new WbSolicitudesController())->findForId($model->fk_id_solicitud, $req->tipo ? $req->tipo : null);
            }

            if ($solicitud != null) {
                $respuesta->put('solicitud', $solicitud['identificador']);
                $respuesta->put('cant_despachada', $solicitud['cant_despachada']);
                $respuesta->put('cant_viajes', $solicitud['cant_viajes']);
            }

            /*if ($this->isSendSmsConfig($this->traitGetProyectoCabecera($req))) {
                $solicitudesTransporte = $this->getTransporte($req->hash);
                $material = data_get($solicitudesTransporte, 'material.Nombre', null);
                $formula = data_get($solicitudesTransporte, 'formula.Nombre', null);
                $material = $material ?? $formula ?? 'Sin material ni fórmula';
                $equipoId = data_get($solicitudesTransporte, 'equipo.equiment_id', 'Equipo desconocido');
                $usuarioId = data_get($solicitudesTransporte, 'solicitud.fk_id_usuarios', null);
                $placa = data_get($solicitudesTransporte, 'equipo.placa', null);
                $cubicaje = data_get($solicitudesTransporte, 'equipo.cubicaje', null);
                $id_usuarios = $usuarioId;
                if ($req->tipo == 1) {
                    $equipoDescripcion = $placa ? $equipoId . ' (' . $placa . ')' : $equipoId;
                    $mensaje = __('messages.sms_synergy_llegada', [
                        'cantidad' => $cubicaje,
                        'material' => $material,
                        'equipoid' => $equipoId,
                        'solicitud' => $req->solicitud_id,
                    ]);
                } else {
                    $equipoDescripcion = $placa ? $equipoId . ' (' . $placa . ')' : $equipoId;
                    $mensaje = __('messages.sms_synergy_despacho', [
                        'cantidad' => $cubicaje,
                        'material' => $material,
                        'equipoid' => $equipoDescripcion,
                        'solicitud' => $req->solicitud_id,
                    ]);
                }
                $nota = __('messages.sms_synergy_despacho_nota');

                //$this->sendSms($mensaje, $nota, $id_usuarios);
            } else {
                \Log::info('No se permite enviar mensajes');
            }*/

            return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
        } catch (\Throwable $th) {
            \Log::error('transport-background ' . $th->getMessage());
            return $this->handleAlert($th->getMessage());
        }
    }

    public function postV2(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric',
                'numero_vale' => 'required|string',
                'tipo' => 'required|numeric',
                'solicitud_id' => 'required|numeric',
                'origen_planta_id' => 'nullable',
                'origen_tramo_id' => 'nullable',
                'origen_tramo_fk_id' => 'nullable',
                'origen_hito_id' => 'nullable',
                'origen_hito_fk_id' => 'nullable',
                'origen_abscisa' => 'nullable',
                'destino_planta_id' => 'nullable',
                'destino_tramo_id' => 'nullable',
                'destino_tramo_fk_id' => 'nullable',
                'destino_hito_id' => 'nullable',
                'destino_hito_fk_id' => 'nullable',
                'destino_abscisa' => 'nullable',
                'cost_center' => 'required',
                'material_id' => 'nullable',
                'formula_id' => 'nullable',
                'equipo_id' => 'required|numeric',
                'equipo_cubicaje' => 'nullable',
                'conductor_dni' => 'nullable|string',
                'cantidad' => 'nullable',
                'usuario_id' => 'required|string',
                'ubicacion' => 'nullable|string',
                'fecha' => 'required|string',
                'observacion' => 'nullable|string',
                'proyecto' => 'required|string',
                'hash' => 'required|string',
                'unique_code' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $solicitud = null;
            $respuesta = collect();
            $respuesta->put('hash', $req->hash);

            $find = WbTransporteRegistro::select('id', 'fk_id_solicitud')->where('hash', $req->hash)->first();
            if ($find != null) {
                $solicitud = (new WbSolicitudesController())->findForIdV2($find->fk_id_solicitud);
            } else {
                $model = new WbTransporteRegistro();

                $model->tipo = $req->tipo ? $req->tipo : null;
                $model->ticket = $req->numero_vale ? $req->numero_vale : null;
                $model->fk_id_solicitud = $req->solicitud_id ? $req->solicitud_id : null;
                $model->fk_id_planta_origen = $req->origen_planta_id ? $req->origen_planta_id : null;

                $model->fk_id_tramo_origen = $req->origen_tramo_id ? $req->origen_tramo_id : null;
                $model->id_tramo_origen = $req->origen_tramo_fk_id ? $req->origen_tramo_fk_id : null;

                $model->fk_id_hito_origen = $req->origen_hito_id ? $req->origen_hito_id : null;
                $model->id_hito_origen = $req->origen_hito_fk_id ? $req->origen_hito_fk_id : null;

                $model->abscisa_origen = $req->origen_abscisa ? $req->origen_abscisa : null;

                $model->fk_id_planta_destino = $req->destino_planta_id ? $req->destino_planta_id : null;

                $model->fk_id_tramo_destino = $req->destino_tramo_id ? $req->destino_tramo_id : null;
                $model->id_tramo_destino = $req->destino_tramo_fk_id ? $req->destino_tramo_fk_id : null;

                $model->fk_id_hito_destino = $req->destino_hito_id ? $req->destino_hito_id : null;
                $model->id_hito_destino = $req->destino_hito_fk_id ? $req->destino_hito_fk_id : null;

                $model->abscisa_destino = $req->destino_abscisa ? $req->destino_abscisa : null;

                $model->fk_id_cost_center = $req->cost_center ? $req->cost_center : null;
                $model->fk_id_material = $req->material_id ? $req->material_id : null;
                $model->fk_id_formula = $req->formula_id ? $req->formula_id : null;
                $model->fk_id_equipo = $req->equipo_id ? $req->equipo_id : null;
                $model->chofer = $req->conductor_dni ? $req->conductor_dni : null;
                $model->observacion = $req->observacion ? $req->observacion : null;
                $model->cantidad = $req->cantidad ? $req->cantidad : null;
                $model->fecha_registro = $req->fecha ? $req->fecha : null;
                $model->estado = 1;
                $model->fk_id_project_Company = $req->proyecto ? $req->proyecto : null;
                $model->ubicacion_gps = $req->ubicacion ? $req->ubicacion : null;
                $model->user_created = $req->usuario_id ? $req->usuario_id : null;
                $model->hash = $req->hash ? $req->hash : null;
                $model->codigo_viaje = $req->unique_code ? $req->unique_code : null;

                if ($req->equipo_cubicaje) {
                    $model->cubicaje = $req->equipo_cubicaje ? $req->equipo_cubicaje : null;
                } else {
                    $equi = WbEquipo::find($model->fk_id_equipo);
                    if ($equi) {
                        $model->cubicaje = $equi->cubicaje ? $equi->cubicaje : null;
                    }
                }

                if (!$model->save()) {
                    return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
                }

                $this->actualizarSolicitud($model);

                $solicitud = (new WbSolicitudesController())->findForIdV2($model->fk_id_solicitud);
            }

            if ($solicitud != null) {
                $respuesta->put('solicitud', $solicitud['identificador']);
                $respuesta->put('total_despachada', $solicitud['total_despachada']);
                $respuesta->put('cant_recibida', $solicitud['cant_recibida']);
                $respuesta->put('cant_viajes_llegada', $solicitud['cant_viajes_llegada']);
                $respuesta->put('cant_despachada', $solicitud['cant_despachada']);
                $respuesta->put('cant_viajes_salida', $solicitud['cant_viajes_salida']);
            }

            /*if ($this->isSendSmsConfig($this->traitGetProyectoCabecera($req))) {
                $solicitudesTransporte = $this->getTransporte($req->hash);
                $material = data_get($solicitudesTransporte, 'material.Nombre', null);
                $formula = data_get($solicitudesTransporte, 'formula.Nombre', null);
                $material = $material ?? $formula ?? 'Sin material ni fórmula';
                $equipoId = data_get($solicitudesTransporte, 'equipo.equiment_id', 'Equipo desconocido');
                $usuarioId = data_get($solicitudesTransporte, 'solicitud.fk_id_usuarios', null);
                $placa = data_get($solicitudesTransporte, 'equipo.placa', null);
                $cubicaje = data_get($solicitudesTransporte, 'equipo.cubicaje', null);
                $id_usuarios = $usuarioId;
                if ($req->tipo == 1) {
                    $equipoDescripcion = $placa ? $equipoId . ' (' . $placa . ')' : $equipoId;
                    $mensaje = __('messages.sms_synergy_llegada', [
                        'cantidad' => $cubicaje,
                        'material' => $material,
                        'equipoid' => $equipoId,
                        'solicitud' => $req->solicitud_id,
                    ]);
                } else {
                    $equipoDescripcion = $placa ? $equipoId . ' (' . $placa . ')' : $equipoId;
                    $mensaje = __('messages.sms_synergy_despacho', [
                        'cantidad' => $cubicaje,
                        'material' => $material,
                        'equipoid' => $equipoDescripcion,
                        'solicitud' => $req->solicitud_id,
                    ]);
                }
                $nota = __('messages.sms_synergy_despacho_nota');

                //$this->sendSms($mensaje, $nota, $id_usuarios);
            } else {
                \Log::info('No se permite enviar mensajes');
            }*/

            return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
        } catch (\Throwable $th) {
            \Log::error('transport-background ' . $th->getMessage());
            return $this->handleAlert($th->getMessage());
        }
    }

    public function postV3(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'identificador' => 'required|numeric',
                'numero_vale' => 'required|string',
                'tipo' => 'required|numeric',
                'solicitud_id' => 'required|numeric',
                'origen_planta_id' => 'nullable',
                'origen_tramo_id' => 'nullable',
                'origen_tramo_fk_id' => 'nullable',
                'origen_hito_id' => 'nullable',
                'origen_hito_fk_id' => 'nullable',
                'origen_abscisa' => 'nullable',
                'destino_planta_id' => 'nullable',
                'destino_tramo_id' => 'nullable',
                'destino_tramo_fk_id' => 'nullable',
                'destino_hito_id' => 'nullable',
                'destino_hito_fk_id' => 'nullable',
                'destino_abscisa' => 'nullable',
                'cost_center' => 'required',
                'material_id' => 'nullable',
                'formula_id' => 'nullable',
                'equipo_id' => 'required|numeric',
                'equipo_cubicaje' => 'nullable',
                'conductor_dni' => 'nullable|string',
                'cantidad' => 'nullable',
                'usuario_id' => 'required|string',
                'ubicacion' => 'nullable|string',
                'fecha' => 'required|string',
                'observacion' => 'nullable|string',
                'proyecto' => 'required|string',
                'hash' => 'required|string',
                'unique_code' => 'nullable|string',
                'tipo_solicitud' => 'required|string',
                'code_bascula' => 'nullable|string',
                'formula' => 'nullable|string',
                'turno' => 'nullable|string',
                'temperatura' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }

            $solicitud = null;
            $respuesta = collect();
            $respuesta->put('hash', $req->hash);

            $find = WbTransporteRegistro::select('id', 'fk_id_solicitud', 'tipo_solicitud')->where('hash', $req->hash)->first();
            if ($find != null) {
                $solicitud = (new WbSolicitudesController())->findForIdV3($find->fk_id_solicitud, $find->tipo_solicitud);
            } else {
                $model = new WbTransporteRegistro();

                $model->tipo = $req->tipo ? $req->tipo : null;
                $model->ticket = $req->numero_vale ? $req->numero_vale : null;
                $model->fk_id_solicitud = $req->solicitud_id ? $req->solicitud_id : null;
                $model->fk_id_planta_origen = $req->origen_planta_id ? $req->origen_planta_id : null;

                $model->fk_id_tramo_origen = $req->origen_tramo_id ? $req->origen_tramo_id : null;
                $model->id_tramo_origen = $req->origen_tramo_fk_id ? $req->origen_tramo_fk_id : null;

                $model->fk_id_hito_origen = $req->origen_hito_id ? $req->origen_hito_id : null;
                $model->id_hito_origen = $req->origen_hito_fk_id ? $req->origen_hito_fk_id : null;

                $model->abscisa_origen = $req->origen_abscisa ? $req->origen_abscisa : null;

                $model->fk_id_planta_destino = $req->destino_planta_id ? $req->destino_planta_id : null;

                $model->fk_id_tramo_destino = $req->destino_tramo_id ? $req->destino_tramo_id : null;
                $model->id_tramo_destino = $req->destino_tramo_fk_id ? $req->destino_tramo_fk_id : null;

                $model->fk_id_hito_destino = $req->destino_hito_id ? $req->destino_hito_id : null;
                $model->id_hito_destino = $req->destino_hito_fk_id ? $req->destino_hito_fk_id : null;

                $model->abscisa_destino = $req->destino_abscisa ? $req->destino_abscisa : null;

                $model->fk_id_cost_center = $req->cost_center ? $req->cost_center : null;
                $model->fk_id_material = $req->material_id ? $req->material_id : null;
                $model->fk_id_formula = $req->formula_id ? $req->formula_id : null;
                $model->fk_id_equipo = $req->equipo_id ? $req->equipo_id : null;
                $model->chofer = $req->conductor_dni ? $req->conductor_dni : null;
                $model->observacion = $req->observacion ? $req->observacion : null;
                $model->cantidad = $req->cantidad ? $req->cantidad : null;
                $model->fecha_registro = $req->fecha ? $req->fecha : null;
                $model->estado = 1;
                $model->fk_id_project_Company = $req->proyecto ? $req->proyecto : null;
                $model->ubicacion_gps = $req->ubicacion ? $req->ubicacion : null;
                $model->user_created = $req->usuario_id ? $req->usuario_id : null;
                $model->hash = $req->hash ? $req->hash : null;
                $model->codigo_viaje = $req->unique_code ? $req->unique_code : null;

                $model->tipo_solicitud = $req->tipo_solicitud ? $req->tipo_solicitud : null;

                $model->code_bascula = $req->code_bascula ? $req->code_bascula : null;

                $model->turno = $req->turno ? $req->turno : null;
                $model->temperatura = $req->temperatura ? $req->temperatura : null;

                if ($req->equipo_cubicaje) {
                    $model->cubicaje = $req->equipo_cubicaje ? $req->equipo_cubicaje : null;
                } else {
                    $equi = WbEquipo::find($model->fk_id_equipo);
                    if ($equi) {
                        $model->cubicaje = $equi->cubicaje ? $equi->cubicaje : null;
                    }
                }

                if (!$model->save()) {
                    return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
                }
                if (!empty($req->origen_hito_id) && !empty($req->destino_hito_id)) {
                    if ($req->origen_hito_id == $req->destino_hito_id) {
                        ViajeInterno::dispatch($model);
                    }
                }



                /* try {
                    $this->actualizarSolicitudV2($model);
                } catch (\Exception $e) {
                    Log::error('error al cerrar solicitud ' . $e->getMessage());
                } */

                TransporteActualizarSolicitud::dispatch($model);


                $solicitud = (new WbSolicitudesController())->findForIdV3($model->fk_id_solicitud, $model->tipo_solicitud);
            }

            if ($solicitud != null) {
                $respuesta->put('solicitud', $solicitud['identificador']);
                $respuesta->put('total_despachada', $solicitud['total_despachada']);
                $respuesta->put('cant_recibida', $solicitud['cant_recibida']);
                $respuesta->put('cant_viajes_llegada', $solicitud['cant_viajes_llegada']);
                $respuesta->put('cant_despachada', $solicitud['cant_despachada']);
                $respuesta->put('cant_viajes_salida', $solicitud['cant_viajes_salida']);
            }

            /* try {
                if ($this->isSendSmsConfig($this->traitGetProyectoCabecera($req))) {
                    $solicitudesTransporte = $this->getTransporte($req->hash);
                    $material = data_get($solicitudesTransporte, 'material.Nombre', null);
                    $formula = data_get($solicitudesTransporte, 'formula.Nombre', null);
                    $material = $material ?? $formula ?? 'Sin material ni fórmula';
                    $equipoId = data_get($solicitudesTransporte, 'equipo.equiment_id', 'Equipo desconocido');
                    $usuarioId = data_get($solicitudesTransporte, 'solicitud.fk_id_usuarios', null);
                    $placa = data_get($solicitudesTransporte, 'equipo.placa', null);
                    $cubicaje = data_get($solicitudesTransporte, 'equipo.cubicaje', null);
                    $id_usuarios = $usuarioId;
                    if ($req->tipo == 1) {
                        $equipoDescripcion = $placa ? $equipoId . ' (' . $placa . ')' : $equipoId;
                        $mensaje = __('messages.sms_synergy_llegada', [
                            'cantidad' => $cubicaje,
                            'material' => $material,
                            'equipoid' => $equipoId,
                            'solicitud' => $req->solicitud_id,
                        ]);
                    } else {
                        $equipoDescripcion = $placa ? $equipoId . ' (' . $placa . ')' : $equipoId;
                        $mensaje = __('messages.sms_synergy_despacho', [
                            'cantidad' => $cubicaje,
                            'material' => $material,
                            'equipoid' => $equipoDescripcion,
                            'solicitud' => $req->solicitud_id,
                        ]);
                    }
                    $nota = __('messages.sms_synergy_despacho_nota');

                    //$this->sendSms($mensaje, $nota, $id_usuarios);
                } else {
                    \Log::info('No se permite enviar mensajes');
                }
            } catch (\Exception $e) {
                Log::info('synergy transporte error mensaje sms: ' . $e->getMessage());
            } */

            return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
        } catch (\Throwable $th) {
            \Log::error('transport-background ' . $th->getMessage());
            return $this->handleAlert($th->getMessage());
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
                    $validacion = Validator::make($info, [
                        'identificador' => 'required|numeric',
                        'numero_vale' => 'required|string',
                        'tipo' => 'required|numeric',
                        'solicitud_id' => 'required|numeric',
                        'origen_planta_id' => 'nullable',
                        'origen_tramo_id' => 'nullable',
                        'origen_tramo_fk_id' => 'nullable',
                        'origen_hito_id' => 'nullable',
                        'origen_hito_fk_id' => 'nullable',
                        'origen_abscisa' => 'nullable',
                        'destino_planta_id' => 'nullable',
                        'destino_tramo_id' => 'nullable',
                        'destino_tramo_fk_id' => 'nullable',
                        'destino_hito_id' => 'nullable',
                        'destino_hito_fk_id' => 'nullable',
                        'destino_abscisa' => 'nullable',
                        'cost_center' => 'required',
                        'material_id' => 'nullable',
                        'formula_id' => 'nullable',
                        'equipo_id' => 'required|numeric',
                        'equipo' => 'nullable|string',
                        'equipo_placa' => 'nullable|string',
                        'equipo_cubicaje' => 'nullable',
                        'conductor_dni' => 'nullable|string',
                        'cantidad' => 'nullable',
                        'usuario_id' => 'required|string',
                        'ubicacion' => 'nullable|string',
                        'fecha' => 'required|string',
                        'observacion' => 'nullable|string',
                        'proyecto' => 'required|string',
                        'hash' => 'required|string',
                        'unique_code' => 'nullable|string',
                        'tipo_solicitud' => 'nullable|string',
                        'code_bascula' => 'nullable|string',
                        'formula' => 'nullable|string',
                        'turno' => 'nullable|string',
                        'temperatura' => 'nullable|string',
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
                        $itemRespuesta->put('solicitud_id', $info['solicitud_id']);
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
                    $model->id_tramo_origen = isset($info['origen_tramo_fk_id']) ? $info['origen_tramo_fk_id'] : null;

                    $model->fk_id_hito_origen = isset($info['origen_hito_id']) ? $info['origen_hito_id'] : null;
                    $model->id_hito_origen = isset($info['origen_hito_fk_id']) ? $info['origen_hito_fk_id'] : null;

                    $model->abscisa_origen = isset($info['origen_abscisa']) ? $info['origen_abscisa'] : null;

                    $model->fk_id_planta_destino = isset($info['destino_planta_id']) ? $info['destino_planta_id'] : null;

                    $model->fk_id_tramo_destino = isset($info['destino_tramo_id']) ? $info['destino_tramo_id'] : null;
                    $model->id_tramo_destino = isset($info['destino_tramo_fk_id']) ? $info['destino_tramo_fk_id'] : null;

                    $model->fk_id_hito_destino = isset($info['destino_hito_id']) ? $info['destino_hito_id'] : null;
                    $model->id_hito_destino = isset($info['destino_hito_fk_id']) ? $info['destino_hito_fk_id'] : null;

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
                    $model->codigo_viaje = isset($info['unique_code']) ? $info['unique_code'] : null;

                    $model->cubicaje = isset($info['equipo_cubicaje']) ? $info['equipo_cubicaje'] : null;

                    $model->tipo_solicitud = isset($info['tipo_solicitud']) ? $info['tipo_solicitud'] : 'M';

                    $model->code_bascula = isset($info['code_bascula']) ? $info['code_bascula'] : null;

                    $model->turno = isset($info['turno']) ? $info['turno'] : null;
                    $model->temperatura = isset($info['temperatura']) ? $info['temperatura'] : null;

                    if (!$model->save()) {
                        continue;
                    }

                    if (!empty($info['origen_hito_id']) && !empty($info['destino_hito_id'])) {
                        if ($info['origen_hito_id'] == $info['destino_hito_id']) {
                            ViajeInterno::dispatch($model);
                        }
                    }

                    $guardados++;
                    $itemRespuesta = collect();
                    $itemRespuesta->put('identificador', $info['identificador']);
                    $itemRespuesta->put('estado', '1');
                    $itemRespuesta->put('solicitud_id', $info['solicitud_id']);
                    $respuesta->push($itemRespuesta);


                    TransporteActualizarSolicitud::dispatch($model);

                    /* try {

                        $this->actualizarSolicitudV2($model);
                    } catch (\Exception $e) {
                        Log::error('actualizar solicitud ' . $e->getMessage());
                    } */

                }

                if ($guardados == 0) {
                    return $this->handleAlert("empty");
                }

                /* $agrupados = collect($listaGuardar)->groupBy('solicitud_id')->map(function ($items) {
                    return [
                        'cantidad_total' => round($items->sum('equipo_cubicaje'), 2),
                        'registros' => $items->count()
                    ];
                });

                Log::info($agrupados); */

                /* try {
                    if ($this->isSendSmsConfig($this->traitGetProyectoCabecera($req))) {
                        foreach ($agrupados as $solicitudId => $datos) {
                            $solicitudesTransporte = $this->getTransporte2($solicitudId);
                            $usuarioId = data_get($solicitudesTransporte, 'solicitud.fk_id_usuarios', null);
                            if ($usuarioId) {
                                $mensaje = __('messages.sms_resumen_solicitud', [
                                    'registros' => $datos['registros'],
                                    'cantidad' => $datos['cantidad_total'],
                                    'solicitud' => $solicitudId
                                ]);
                                $nota = __('messages.sms_resumen_nota');

                                $this->sendSms($mensaje, $nota, $usuarioId);
                            } else {
                                \Log::warning("Usuario no encontrado para solicitud ID: $solicitudId");
                            }
                        }
                    } else {
                        \Log::info('No se permite enviar mensajes');
                    }
                } catch (\Exception $e) {
                    Log::info('synergy transporte error mensaje sms: ' . $e->getMessage());
                } */
                return $this->handleResponse($req, $respuesta, __('messages.registro_exitoso'));
            } else {
                return $this->handleAlert("empty");
            }
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return $this->handleAlert($th->getMessage());
        }
    }

    /**
     * Get por transporte de materiales
     */
    public function getTransporte($hash)
    {
        $consulta = WbTransporteRegistro::where('hash', $hash)->with(['equipo', 'material', 'solicitud', 'formula']);
        $resultados = $consulta->first();
        return $resultados;
    }


    public function getTransporte2($id_solicitud)
    {
        $consulta = WbTransporteRegistro::where('fk_id_solicitud', $id_solicitud)->with(['equipo', 'material', 'solicitud', 'formula']);
        $resultados = $consulta->first();
        return $resultados;
    }




    /***
     * Actualizar el estado de solicitud de material,
     * si este fue despachado completamente
     */
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
        $solicitud->user_despacho = $item->user_created;

        if ($solicitud->save()) {
            if ($this->isSendSmsConfig($item->fk_id_project_Company)) {
                $solicitudesTransporte = $this->getTransporte($item->hash);
                $usuarioId = data_get($solicitudesTransporte, 'solicitud.fk_id_usuarios', null);
                $id_usuarios = $usuarioId;
                $mensaje = __('messages.sms_synergy_despacho_cerrar', [
                    'solicitud' => $item->fk_id_solicitud
                ]);
                $nota = __('messages.sms_synergy_despacho_nota');
                $this->sendSms($mensaje, $nota, $id_usuarios);
            } else {
                Log::info('No se permite enviar mensajes');
            }
        }
    }

    public function actualizarSolicitudV2(WbTransporteRegistro $item)
    {
        if ($item->tipo_solicitud == 'M') {
            $this->actualizarSolicitudMaterial($item);
        } else {
            $this->actualizarSolicitudAsfalto($item);
        }
    }

    private function actualizarSolicitudMaterial(WbTransporteRegistro $item)
    {
        $solicitud = WbSolicitudMateriales::where('id_solicitud_Materiales', $item->fk_id_solicitud)
            ->where('fk_id_project_Company', $item->fk_id_project_Company)
            ->with([
                'transporte' => function ($sub) {
                    $sub->with('equipo')->where('tipo', 2)->where('estado', 1);
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
        $solicitud->user_despacho = $item->user_created;

        if ($solicitud->save()) {
            try {
                if ($this->isSendSmsConfig($item->fk_id_project_Company)) {
                    $solicitudesTransporte = $this->getTransporte($item->hash);
                    $usuarioId = data_get($solicitudesTransporte, 'solicitud.fk_id_usuarios', null);
                    $id_usuarios = $usuarioId;
                    $mensaje = __('messages.sms_synergy_despacho_cerrar', [
                        'solicitud' => $item->fk_id_solicitud
                    ]);
                    $nota = __('messages.sms_synergy_despacho_nota');
                    $this->sendSms($mensaje, $nota, $id_usuarios);
                } else {
                    Log::info('No se permite enviar mensajes');
                }
            } catch (\Throwable $th) {
                Log::error('error enviar sms solicitud asfalto ' . $th->getMessage());
            }
        }
    }

    private function actualizarSolicitudAsfalto(WbTransporteRegistro $item)
    {
        $solicitud = WbSolitudAsfalto::where('id_solicitudAsf', $item->fk_id_solicitud)
            ->where('fk_id_project_Company', $item->fk_id_project_Company)
            ->with([
                'transporte' => function ($sub) {
                    $sub->where('tipo', 2)->where('estado', 1);
                }
            ])
            ->first();

        // Consultamos si encontramos una solicitud
        if (!$solicitud) {
            return;
        }

        // Consultamos que la solicitud no ha sido despachada en su totalidad
        if ($solicitud->estado && $solicitud->estado == 'ENVIADO') {
            return;
        }

        // Consultamos si la solicitud tiene por lo menos algun transporte registrado
        if (!$solicitud->transporte) {
            return;
        }

        $cantidad = 0;

        $cantidad = $solicitud->transporte->filter(fn($tr) => $tr->cantidad != null)
            ->sum(fn($tr) => $tr->cantidad ?? 0);

        $redondear = ceil($cantidad ?? 0);

        $total = $redondear > 0 ? ($redondear / 1000) : 0;

        // Convertir el valor de la cantidad fuera del condicional
        $convertCantidad = floatval($solicitud->cantidadToneladas);

        $cantidadNecesaria = $convertCantidad + ($convertCantidad * 0.15);

        Log::error('cantidad dedondeada ' . $total . ' cantidad necesaria ' . $cantidadNecesaria);
        if ($cantidadNecesaria <= $total) {
            // Asignar valores y guardar la solicitud solo si pasa la validación
            $solicitud->estado = 'ENVIADO';
            $solicitud->fecha_cierre = Carbon::now()->format('d/m/Y h:i:s A');
            $solicitud->user_despacho = $item->user_created;
            $solicitud->toneFaltante = 0;
        } else {
            $solicitud->toneFaltante = abs($total - $convertCantidad);
        }

        if ($solicitud->save()) {

            $modelo = new PlanillaControlAsfalto();

            $carbonFecha = Carbon::parse($item->fecha_registro);
            $fecha = $carbonFecha->format('j/n/Y');    // "2025-01-23"
            $hora = $carbonFecha->format('h:i A');    // "10:10 AM"

            $tranport = WbTransporteRegistro::where('id', $item->id)->with('formulaAsf', 'equipo', 'destinoPlanta')->first();

            $modelo->fk_solicitud = $item->fk_id_solicitud;
            $modelo->placaVehiculo = $tranport && $tranport->equipo ? $tranport->equipo->placa : null;
            $modelo->codigoVehiculo = $tranport && $tranport->equipo ? $tranport->equipo->equiment_id : null;
            $modelo->hora = $hora;
            $modelo->wbeDestino = $solicitud->CostCode;
            $modelo->descripDestino = $tranport->destinoPlanta ? $tranport->destinoPlanta->planta : ($tranport->fk_id_tramo_destino ?
                __('messages.tramo') . ' ' . $tranport->fk_id_tramo_destino . ' ' . ($tranport->fk_id_hito_destino ?
                    __('messages.hito') . ' ' . $tranport->fk_id_hito_destino :
                    '') :
                null);
            $modelo->formula = $tranport->formulaAsf ? $tranport->formulaAsf->asfalt_formula : null;
            $modelo->cantidad = $solicitud->cantidadToneladas;
            $modelo->firma = '--';
            $modelo->observacion = 'Enviado';
            $modelo->fecha = $fecha;
            $modelo->fk_id_usuario = $item->user_created;
            $modelo->cantiEnviada = $tranport->cantidad / 1000;
            $modelo->turno = $item->turno == 1 ? __('messages.diurno') : __('messages.nocturno');
            $modelo->plantaDespacho = $item->fk_id_planta_origen ? $item->fk_id_planta_origen : null;
            $modelo->codeqr = $tranport->ticket;
            $modelo->temperatura = $tranport->temperatura;
            $modelo->estado = 1;
            $modelo->save();

            if ($cantidadNecesaria <= $total) {
                try {
                    if ($this->isSendSmsConfig($item->fk_id_project_Company)) {
                        $solicitudesTransporte = $this->getTransporte($item->hash);
                        $usuarioId = data_get($solicitudesTransporte, 'solicitud.fk_id_usuarios', null);
                        $id_usuarios = $usuarioId;
                        $mensaje = __('messages.sms_synergy_despacho_cerrar', [
                            'solicitud' => $item->fk_id_solicitud
                        ]);
                        $nota = __('messages.sms_synergy_despacho_nota');
                        $this->sendSms($mensaje, $nota, $id_usuarios);
                    } else {
                        Log::info('No se permite enviar mensajes');
                    }
                } catch (\Throwable $th) {
                    Log::error('error enviar sms solicitud asfalto ' . $th->getMessage());
                }
            }
        }
    }

    /**
     * Consulta por proyecto si en su configuracion esta disponible el envio de sms
     * @param mixed $proyecto identificador del proyecto
     * @return bool {true = si esta disponible enviar sms, false = no esta disponible para sms}
     */
    private function isSendSmsConfig($proyecto)
    {
        $enviar_sms = WbConfiguraciones::select('enviar_mensajes')
            ->where('fk_id_project_Company', $proyecto)
            ->first();

        return $enviar_sms && $enviar_sms->enviar_mensajes == 1;
    }

    /**
     * Envia mensaje de sms con la informacion suministrada
     * @param mixed $mensaje
     * @param mixed $nota
     * @param mixed $usuarios
     * @return void
     */
    private function sendSms($mensaje, $nota, $usuarios)
    {
        try {
            $confirmationController = new SmsController();
            $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $usuarios);
        } catch (\Throwable $e) {
            \Log::error('sendSms error: ' . $e);
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
