<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\actividad;
use App\Models\estado;
use App\Models\liberacionesActividades;
use App\Models\solicitudConcreto;
use App\Models\usuarios_M;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LiberacionesActividadesController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
        if (!$req->json()->has('fk_act')) {
            return $this->handleAlert('Ingrese la actividad.');
        }
        if (!$req->json()->has('fk_solicitud')) {
            return $this->handleAlert('Ingrese la solicitud');
        }
        if (!$req->json()->has('fk_estado')) {
            return $this->handleAlert('Ingrese el estado');
        }
        if (!$req->json()->has('fk_usuario')) {
            return $this->handleAlert('Ingrese el usuario');
        }
        if (!$req->json()->has('observacion')) {
            return $this->handleAlert('Ingrese la observacion');
        }
        if ($req->validate([
            'fk_act' => 'required|numeric',
            'fk_solicitud' => 'required|numeric',
            'fk_estado' => 'required|numeric',
            'fk_usuario' => 'required|numeric',
            'observacion' => 'required|string',
        ])) {
            try {
                $actividadBuscada = actividad::where('id_Actividad', '=', $req->fk_act)->get();
                $solicitudBuscada = solicitudConcreto::where('id_solicitud', '=', $req->fk_solicitud)->get();
                $estadoBuscado = estado::where('id_estados', '=', $req->fk_estado)->get();
                $usuarioBuscado = usuarios_M::where('id_usuarios', '=', $req->fk_usuario)->get();
                if (count($actividadBuscada) == 0) {
                    return $this->handleAlert('Actividad no encontrada.');
                }
                if (count($solicitudBuscada) == 0) {
                    return $this->handleAlert('Solicitud no encontrada.');
                }
                if (count($estadoBuscado) == 0) {
                    return $this->handleAlert('Estado no encontrado.');
                }
                if (count($usuarioBuscado) == 0) {
                    return $this->handleAlert('Usuario no encontado.');
                }
                $liberacionActividad = new liberacionesActividades;
                $liberacionActividad->fk_act = $req->fk_act;
                $liberacionActividad->fk_solicitud = $req->fk_solicitud;
                $liberacionActividad->fk_estado = $req->fk_estado;
                $liberacionActividad->fk_usuario = $req->fk_usuario;
                $liberacionActividad->observacion = $req->observacion;
                $liberacionActividad = $this->traitSetProyectoYCompania($req, $liberacionActividad);
                $liberacionActividad->save();
                return $this->handleResponse($req, $liberacionActividad, 'Liberacion de actividad registrada guardada en el sistema.');
            } catch (Exception $exc) {
                return $this->handleAlert('No se encontraron datos que coincidan con los criterios.');
            }
        }
    }


    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function get(Request $request)
    {
        // TODO: Implement get() method.
    }

    public function modificarPorActividadYSolicitud(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'actividad' => 'required|string',
                'estado' => 'required|string',
                'observacion' => 'required|string',
                'solicitud' => 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->handleAlert($validator->errors());
            }
            liberacionesActividades::where('fk_solicitud', $request->solicitud)
                ->where('fk_act', $request->actividad)
                ->update([
                    'fk_estado' => $request->estado,
                    'fk_usuario' => $this->traitGetIdUsuarioToken($request),
                    'observacion' => $request->observacion
                ]);
            return $this->handleResponse($request, [], __('messages.consultado'));
        } catch (Exception $exc) {
            var_dump($exc);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
