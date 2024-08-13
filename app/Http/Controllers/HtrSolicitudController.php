<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\HtrSolicitud;
use App\Models\solicitudConcreto;
use Illuminate\Http\Request;

class HtrSolicitudController extends BaseController implements Vervos
{

    public function post(Request $req)
    {
        if (solicitudConcreto::find($req->solicitud) == null) {
            return $this->handleAlert('messages.solicitud_concreto_no_encontrada');
        }
        if (!(strcmp($req->estado, 'REVISADO') == 0 || strcmp($req->estado, 'PENDIENTE') == 0)) {
            return $this->handleAlert(__('messages.estado_no_valido'));
        }
        $modeloRegistrar = new HtrSolicitud;
        $modeloRegistrar->fk_id_solicitud = $req->solicitud;
        $modeloRegistrar->estado = $req->estado;
        $modeloRegistrar->horaVisto = $req->horaVisto;
        $modeloRegistrar->vistoSN = $req->vistoSN;
        $modeloRegistrar->nomEquipo = $req->nomEquipo;
        $modeloRegistrar->VistoPor_id_usuario = $this->traitGetIdUsuarioToken($req);
        if ($modeloRegistrar->save()) {
            return $this->handleResponse($req, [], __('messages.solicitud_registrada'));
        } else {
            return $this->handleAlert('messages.solicitud_no_registrada');
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
        try {
            $page = $request->page;
            $limit = $request->limit;
            $consulta = HtrSolicitud::select('htrSolicitud.*');
            $consultaCount = HtrSolicitud::select('id_htrSolicitud');
            $consulta = $this->filtrar($request, $consulta)->orderBy('id_htrSolicitud', 'desc');
            $consultaCount = $this->filtrar($request, $consultaCount)->orderBy('id_htrSolicitud', 'desc');
            $rows = $consultaCount->get()->count();
            $consulta = $consulta->forPage($page, $limit)->get();
            $limitePaginas = ($rows/$limit) + 1;
            return $this->handleResponse($request, $this->htrSolicitudToArray($consulta), __("messages.consultado"), $limitePaginas);
        } catch (\Exception $exc) {}
    }

    public function getApp() {

    }

    public function getPorProyecto(Request $request, $proyecto)
    {


    }

}
