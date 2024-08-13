<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use App\Http\interfaces\Vervos;
use App\Models\Wb_Solicitud_Liberaciones_Firmas_M;
use App\Models\Wb_Solicitud_Liberaciones_Act;
use App\Http\Controllers\Wb_Solicitud_Liberaciones;
use App\Models\wbSolicitudLiberaciones;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;

class WbSolicitudLiberacionesFirmasController extends BaseController implements Vervos
{
    /*
     * CAMBIAR ESTADO DE UNA CALIFICACION
     */
    public function FirmarDeprecated(Request $actividad)
    {
        try {
            $datos = $actividad->all();
            $actividades = Wb_Solicitud_Liberaciones_Firmas_M::find($datos['IDSOLICITUDESLIBERACIONFIRMAS']);
            $calificacion = Wb_Solicitud_Liberaciones_Act::join('Wb_Liberaciones_Reponsable as lr', 'Wb_Solicitud_Liberaciones_Act.fk_id_liberaciones_actividades', '=', 'lr.fk_id_liberaciones_actividades')->where('Wb_Solicitud_Liberaciones_Act.fk_id_solicitud_liberaciones', $actividades->fk_id_solicitudes_liberaciones)->where('lr.fk_id_area', $actividades->fk_id_area)->whereNull('Wb_Solicitud_Liberaciones_Act.calificacion')->get();
            if ($calificacion->count() > 0) {
                return $this->handleError('Error', 'Antes de poder firmar debe calificar todas las actividades', 200);
            }
            $now = new \DateTime();
            $actividades->fk_id_usuario = $datos['IDUSUARIO'];
            $actividades->nota = $datos['NOTA'];
            $actividades->panoramica = $datos['PANORAMICA'];
            $actividades->estado = 2;
            $actividades->dateCreate = $now->format('d-m-Y H:i:s');
            $actividades->ubicacionGPS = $datos['UBICACION'];
            $actividades->save();
            $actividades->refresh();
            return $this->handleAlert(1, true);
        } catch (\Throwable $th) {
            $datos = $actividad->all();
            Log::debug('Error al insertar la firma \n mensaje: ' . $th->getMessage());
            return $this->handleError('Error', 'Error al guardar la firma, comuniquese con el area de Sistemas' . $th->getMessage(), 200);
        }
    }

    /*
     * CAMBIAR ESTADO DE UNA CALIFICACION
     */
    public function Firmar(Request $req)
    {
        try {
            $usuario = $this->traitGetIdUsuarioToken($req);

            $actividades = Wb_Solicitud_Liberaciones_Firmas_M::find($req->IDSOLICITUDESLIBERACIONFIRMAS);
            $calificacion = Wb_Solicitud_Liberaciones_Act::join('Wb_Liberaciones_Reponsable as lr', 'Wb_Solicitud_Liberaciones_Act.fk_id_liberaciones_actividades', '=', 'lr.fk_id_liberaciones_actividades')
                ->where('Wb_Solicitud_Liberaciones_Act.fk_id_solicitud_liberaciones', $actividades->fk_id_solicitudes_liberaciones)
                ->where('lr.fk_id_area', $actividades->fk_id_area)
                ->whereNull('Wb_Solicitud_Liberaciones_Act.calificacion')
                ->count();



            if ($calificacion > 0) {
                return $this->handleAlert('Antes de poder firmar debe calificar todas las actividades');
            }

            $solicitante = wbSolicitudLiberaciones::select('fk_id_usuarios')->find($actividades->fk_id_solicitudes_liberaciones);

            Log::info($solicitante);

            $now = new \DateTime();
            $actividades->fk_id_usuario = $usuario;
            $actividades->nota = $req->NOTA;
            $actividades->panoramica = $req->PANORAMICA;
            $actividades->estado = 2;
            $actividades->dateCreate = $now->format('d-m-Y H:i:s');
            $actividades->ubicacionGPS = $req->UBICACION;

            if (!$actividades->save()) {
                return $this->handleAlert('No se pudo guardar la firma');
            }
            $actividades->refresh();
            $confirmationController = new SmsController();
            //$id_usuarios = $this->traitGetIdUsuarioToken($req);
            //$id_usuarios = $solicitante;
            //mensaje al solicitante
            $mensaje = 'WEBU, Su solicitud de liberacion  de capas No. ' . $actividades->fk_id_solicitudes_liberaciones . ' ha sido firmada por el area de ' . $actividades->area->Area;
            //mensaje a la persona que firma
            $mensaje2 = 'WEBU, Ha firmado la solicitud de liberacion  de capas No. ' . $actividades->fk_id_solicitudes_liberaciones . '.';
            $nota = 'Solicitar Liberaciones';
            $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $solicitante->fk_id_usuarios);
            $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje2, $nota, $usuario);
            $this->Finalizar_liberacion($req, $actividades->fk_id_solicitudes_liberaciones);
            return $this->handleAlert(1, true);
        } catch (\Throwable $th) {
            Log::debug('Error al insertar la firma \n mensaje: ' . $th->getMessage());
            return $this->handleAlert('Error al guardar la firma, comuniquese con el area de Sistemas' . $th->getMessage());
        }
    }

    public function firmarV2(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'IDSOLICITUDESLIBERACIONFIRMAS' => 'required|string',
                'PANORAMICA' => 'required|string',
                'NOTA' => 'required|string',
                'UBICACION' => 'required|string',
                'CALIFICACIONES' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->handleAlert($validator->messages());
            }

            $usuario = $this->traitGetIdUsuarioToken($req);

            $actividades = Wb_Solicitud_Liberaciones_Firmas_M::find($req->IDSOLICITUDESLIBERACIONFIRMAS);

            $calificaciones = (new Wb_solicitud_liberaciones_act_controller())->cambiarEstadoV2($req);

            if (!$calificaciones) {
                return $this->handleAlert('Error al registrar calificaciones por favor volver a intentar');
            }


            $solicitante = wbSolicitudLiberaciones::select('fk_id_usuarios')->find($actividades->fk_id_solicitudes_liberaciones);

            Log::info($solicitante);

            $now = new \DateTime();
            $actividades->fk_id_usuario = $usuario;
            $actividades->nota = $req->NOTA;
            $actividades->panoramica = $req->PANORAMICA;
            $actividades->estado = 2;
            $actividades->dateCreate = $now->format('d-m-Y H:i:s');
            $actividades->ubicacionGPS = $req->UBICACION;

            if (!$actividades->save()) {
                return $this->handleAlert('No se pudo guardar la firma');
            }
            $actividades->refresh();

            $confirmationController = new SmsController();
            $mensaje = 'WEBU, Su solicitud de liberacion  de capas No. ' . $actividades->fk_id_solicitudes_liberaciones . ' ha sido firmada por el area de ' . $actividades->area->Area;
            $mensaje2 = 'WEBU, Ha firmado la solicitud de liberacion  de capas No. ' . $actividades->fk_id_solicitudes_liberaciones . '.';
            $nota = 'Solicitar Liberaciones';
            try {
                $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $solicitante->fk_id_usuarios);
                $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje2, $nota, $usuario);
            } catch (\Throwable $th) {
                //throw $th;
            }
            $this->Finalizar_liberacion($req, $actividades->fk_id_solicitudes_liberaciones);
            return $this->handleAlert(1, true);
        } catch (\Throwable $th) {
            Log::debug('Error al insertar la firma \n mensaje: ' . $th->getMessage());
            return $this->handleAlert('Error al guardar la firma, comuniquese con el area de Sistemas' . $th->getMessage());
        }
    }


    /**
     * Funcion que cambia el estado a liberados una vez se firman las liberaciones
     */
    public function Finalizar_liberacion($req, $id_solicitud_liberacion)
    {

        try {
            //$actividades = Wb_Solicitud_Liberaciones_Firmas_M::find($req->IDSOLICITUDESLIBERACIONFIRMAS);
            $solicitante = wbSolicitudLiberaciones::where('id_solicitud_liberaciones', $id_solicitud_liberacion)->first();
            $firmas_pendientes = Wb_Solicitud_Liberaciones_Firmas_M::where('fk_id_solicitudes_liberaciones', $id_solicitud_liberacion)
                ->whereNull('fk_id_usuario')
                ->count();
            $calificacion_pendiente = Wb_Solicitud_Liberaciones_Act::where('fk_id_solicitud_liberaciones', $id_solicitud_liberacion)
                ->where(function ($query) {
                    $query->whereNull('calificacion')
                        ->orWhere('calificacion', 'NC');
                })
                ->get();
            $calificacion_pendiente_filtered = $calificacion_pendiente->filter(function ($item) {
                return $item->calificacion === 'NC';
            });

            if ($calificacion_pendiente_filtered->count() > 0) {
                wbSolicitudLiberaciones::where('id_solicitud_liberaciones', $id_solicitud_liberacion)
                    ->where('fk_id_estados', 12)
                    ->update(['fk_id_estados' => 13]);
                try {
                    $confirmationController = new SmsController();
                    $id_usuarios = $solicitante->fk_id_usuarios->fk_id_usuarios;
                    try {
                        $mensaje = 'WEBU, Su solicitud de liberacion de capas No. ' . $id_solicitud_liberacion . ' ha sido rechazada';
                        $nota = 'Liberaciones de capa';
                        $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                    } catch (\Throwable $e) {
                    }
                } catch (\Exception $e) {
                    Log::debug('Error al enviar el mensaje de confirmacion', $e->getMessage());
                }
            } else if ($firmas_pendientes == 0) {
                if ($calificacion_pendiente->count() == 0) {
                    wbSolicitudLiberaciones::where('id_solicitud_liberaciones', $id_solicitud_liberacion)
                        ->where('fk_id_estados', 12)
                        ->update(['fk_id_estados' => 25]);
                    $confirmationController = new SmsController();
                    $id_usuarios = $solicitante->fk_id_usuarios;
                    try {
                        $mensaje = 'WEBU, Su solicitud de liberacion  de capas No. ' . $id_solicitud_liberacion . ' ha sido firmada por todas las areas';
                        $nota = 'Liberaciones de capa';
                        $confirmationController->Enviar_Sms_Por_IdUsuarios($mensaje, $nota, $id_usuarios);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                } else {
                    wbSolicitudLiberaciones::where('id_solicitud_liberaciones', $id_solicitud_liberacion)
                        ->where('fk_id_estados', 12)
                        ->update(['fk_id_estados' => 26]);
                }
            }
            return $this->handleAlert(1, true);
        } catch (\Throwable $th) {
            Log::debug('Error al insertar la firma \n mensaje: ' . $th->getMessage());
            $this->handleAlert('Error al guardar la firma, comuniquese con el area de Sistemas' . $th->getMessage());
        }
    }


    /**
     * @param Request $req
     * @return void
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    /**
     * @param Request $req
     * @param $id
     * @return void
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $id
     * @return void
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    /* public function get(Request $request) //04/07/2024 obsoleto
    {
        $consulta = Wb_Solicitud_Liberaciones_Firmas_M::select(
            'id_solicitudes_liberaciones_firmas',
            'fk_id_solicitudes_liberaciones',
            'fk_id_area',
            'fk_id_usuario',
            'Wb_Solicitud_Liberaciones_Firmas.estado',
            'dateCreate',
            'Area.id_area',
            'Area.Area',
            'nota',
            'panoramica'
        )->leftJoin('Area', 'Area.id_area', 'Wb_Solicitud_Liberaciones_Firmas.fk_id_area');
        if ($request->has('id') != null && is_numeric($request->id)) {
            $consulta = $consulta->where('Wb_Solicitud_Liberaciones_Firmas.fk_id_solicitudes_liberaciones', $request->id);
        }

        $consulta = $this->filtrar($request, $consulta, 'Wb_Solicitud_Liberaciones_Firmas')->get();

        return $this->handleResponse($request, $this->solicitudLiberacionFirmaToArray($consulta), __('messages.consultado'));
    } */

    public function get(Request $request)
    {
        $consulta = Wb_Solicitud_Liberaciones_Firmas_M::with([
            'area' => function ($consulta) {
                $consulta->select('id_area', 'Area');
            }
        ])->select(
                'id_solicitudes_liberaciones_firmas',
                'fk_id_solicitudes_liberaciones',
                'fk_id_area',
                'fk_id_usuario',
                'estado',
                'dateCreate',
                'nota',
                'panoramica'
            );

        if ($request->has('id') != null && is_numeric($request->id)) {
            $consulta = $consulta->where('fk_id_solicitudes_liberaciones', $request->id);
        }

        $consulta = $this->filtrar($request, $consulta, 'Wb_Solicitud_Liberaciones_Firmas')->get();

        return $this->handleResponse($request, $this->solicitudLiberacionFirmaToArray($consulta), __('messages.consultado'));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
