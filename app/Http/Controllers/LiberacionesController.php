<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Liberaciones;
use App\Models\solicitudConcreto;
use App\Models\usuarios_M;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiberacionesController extends BaseController implements Vervos
{
    public function firmarProduccion(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Solicitud no valida.');
        }
        if(!$req->json()->has('usuarios')) {
            return $this->handleAlert('Ingrese el usuario.');
        }
        if(!$req->json()->has('firmaCalidad')) {
            return $this->handleAlert('Ingrese la firmaCalidad.');
        }
        if(!$req->json()->has('firmaProduccion')) {
            return $this->handleAlert('Ingrese la firmaProduccion.');
        }
        if(!$req->json()->has('firmaTopografia')) {
            return $this->handleAlert('Ingrese la firmaTopografia.');
        }
        if(!$req->json()->has('firmaLaboratorio')) {
            return $this->handleAlert('Ingrese la firmaLaboratorio.');
        }
        if(!$req->json()->has('firmaSST')) {
            return $this->handleAlert('Ingrese la firmaSST.');
        }
        if(!$req->json()->has('firmaAmbiental')) {
            return $this->handleAlert('Ingrese la firmaAmbiental.');
        }
        if($req->validate([
            'usuarios' => '',
            'firmaCalidad' => '',
            'firmaProduccion' => '',
            'firmaTopografia' => '',
            'firmaLaboratorio' => '',
            'firmaSST' => '',
            'firmaAmbiental' => ''
        ])) {
            if(solicitudConcreto::find($id) == null) {
                return $this->handleAlert('SOlicitud no encontrada.');
            }
            $modeloModificar = Liberaciones::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert('Liberacion no encontrada');
            }
            if($req->firmaCalidad == 'YES') {
                $modeloModificar->firmaCalidad = $req->usuarios;
            }
            if($req->firmaCalidad == 'PENDIENTE') {
                $modeloModificar->firmaCalidad = $req->firmaCalidad;
            }
            if($req->firmaProduccion == 'YES') {
                $modeloModificar->firmaProduccion = $req->usuarios;
            }
            if($req->firmaProduccion == 'PENDIENTE') {
                $modeloModificar->firmaProduccion = $req->firmaProduccion;
            }
            if($req->firmaTopografia == 'YES') {
                $modeloModificar->firmaTopografia = $req->usuarios;
            }
            if($req->firmaTopografia == 'PENDIENTE') {
                $modeloModificar->firmaTopografia = $req->firmaTopografia;
            }
            if($req->firmaLaboratorio == 'YES') {
                $modeloModificar->firmaLaboratorio = $req->usuarios;
            }
            if($req->firmaLaboratorio == 'PENDIENTE') {
                $modeloModificar->firmaLaboratorio = $req->firmaLaboratorio;
            }
            if($req->firmaSST == 'YES') {
                $modeloModificar->firmaSST = $req->usuarios;
            }
            if($req->firmaSST == 'PENDIENTE') {
                $modeloModificar->firmaSST = $req->firmaSST;
            }
            if($req->firmaAmbiental == 'YES') {
                $modeloModificar->firmaAmbiental = $req->usuarios;
            }
            if($req->firmaAmbiental == 'PENDIENTE') {
                $modeloModificar->firmaAmbiental = $req->firmaAmbiental;
            }
            try {
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Solicitud firmada.');
                }
            } catch(Exception $exc) {}
            return $this->handleAlert('Solicitud no firmada.');
        }
    }

    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function numeroSolicitudesLiberadas(Request $request) {
       $consulta = Liberaciones::leftJoin('SolicitudConcreto', 'SolicitudConcreto.id_solicitud', '=', 'Liberaciones.fk_solicitud')
       ->where('SolicitudConcreto.estado', '!=', 'ANULADO')
       ->where('SolicitudConcreto.estado', '!=', 'BORRADOR')
       ->whereNotNull('firmaTopografia')
       ->whereNotNull('firmaLaboratorio')
       ->whereNotNull('firmaProduccion')
       ->whereNotNull('firmaCalidad')
       ->whereNotNull('firmaSST')
       ->whereNotNull('firmaAmbiental')
       ->where('Liberaciones.fk_usuario', '!=', 1)
       ->where('Liberaciones.fk_usuario', '!=', 33601)
       ->where(Db::raw("CONVERT (datetime, Liberaciones.fechaHoraSolicitud, 103)"), '>=','19/1/2022' )
       ->get()->count();
       return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    public function get(Request $request)
    {
        // TODO: Implement get() method.
        $consulta = Liberaciones::select('Liberaciones.*');
        $consultaCount = Liberaciones::select('fk_solicitud');
        $consulta = $this->filtrar($request, $consulta)->orderBy('fk_solicitud', 'desc');
        $consultaCount = $this->filtrar($request, $consultaCount);
        // si se va a consultar por solicitud, la consulta siempre va a retonar como maximo 1 resultado
        if ($request->has('solicitud')) {
            $consulta = $consulta->where('fk_solicitud', $request->solicitud)->first();
            if ($consulta == null) {
                return $this->handleAlert( __("messages.liberacion_no_encontrada"));
            }
            $usuarios = usuarios_M::all();
            $this->setFirmaAmbiental($consulta, $usuarios);
            $this->setFirmaCalidad($consulta, $usuarios);
            $this->setFirmaProduccion($consulta, $usuarios);
            $this->setFirmaSst($consulta, $usuarios);
            $this->setFirmaTopografia($consulta, $usuarios);
            $this->setFirmaLaboratorio($consulta, $usuarios);
            return $this->handleResponse($request, $this->liberacionesToModel($consulta), __("messages.consultado"));
        }
        $page = $request->page;
        $limit = $request->limit;
        if(!is_numeric($page) || !is_numeric($limit)) {
            return $this->handleResponse($request, [], 'Consultado', 1);
        }
        $consulta = $consulta->forPage($page, $limit)->get();
        $rows = $consultaCount->get()->count();
        $limitePaginas = ($rows/$limit) + 1;
        return $this->handleResponse($request, $this->liberacionesToArray($consulta), __("messages.consultado"), $limitePaginas);
    }

    private function setFirmaAmbiental($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->firmaAmbiental == $array[$i]->id_usuarios) {
                $reescribir = $this->usuarioToModel($array[$i]);
                $estructura->objectFirmaAmbiental = $reescribir;
                break;
            }
        }
    }

    private function setFirmaCalidad($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->firmaCalidad == $array[$i]->id_usuarios) {
                $reescribir = $this->usuarioToModel($array[$i]);
                $estructura->objectFirmaCalidad = $reescribir;
                break;
            }
        }
    }

    private function setFirmaProduccion($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->firmaProduccion == $array[$i]->id_usuarios) {
                $reescribir = $this->usuarioToModel($array[$i]);
                $estructura->objectFirmaProduccion = $reescribir;
                break;
            }
        }
    }

    private function setFirmaSst($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->firmaSST == $array[$i]->id_usuarios) {
                $reescribir = $this->usuarioToModel($array[$i]);
                $estructura->objectFirmaSst = $reescribir;
                break;
            }
        }
    }

    private function setFirmaTopografia($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->firmaTopografia == $array[$i]->id_usuarios) {
                $reescribir = $this->usuarioToModel($array[$i]);
                $estructura->objectFirmaTopografia = $reescribir;
                break;
            }
        }
    }

    private function setFirmaLaboratorio($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->firmaLaboratorio == $array[$i]->id_usuarios) {
                $reescribir = $this->usuarioToModel($array[$i]);
                $estructura->objectFirmaLaboratorio = $reescribir;
                break;
            }
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
