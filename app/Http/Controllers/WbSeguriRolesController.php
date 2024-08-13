<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbSeguriRoles;
use Exception;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class WbSeguriRolesController extends BaseController implements Vervos
{
    public function isAdmin(Request $req) {
        $token = $this->traitGetTokenCabecera($req);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        if($miProyecto == null || $tokenPersonal == null) {
            return $this->handleAlert('Permiso no valido.');
        }
        $rolAdmin = $this->traitGetRolAdmin();
        if ($miProyecto->fk_rol == $rolAdmin->id_Rol) {
            return $this->handleResponse($req, '', '');
        } else {
            return $this->handleAlert('Permiso no valido.');
        }
    }

    public function post(Request $req)
    {
        // TODO: Implement post() method.
        if(!$req->json()->has('nombreRol')) {
            return $this->handleAlert('Ingrese el campo nombre', false);
        }
        $consultas = WbSeguriRoles::where('nombreRol', $req->nombreRol)->get();
        if($consultas->count() > 0) {
            return $this->handleAlert('Este nombre de rol ya se encuentra en uso.', false);
        }
        $rolRegistrar = new WbSeguriRoles;
        $rolRegistrar->nombreRol = $req->nombreRol;
        $rolRegistrar = $this->traitSetProyectoYCompania($req, $rolRegistrar);
        try {
            if($rolRegistrar->save()) {
                return $this->handleResponse($req, [], 'Registrado.');
            }
        } catch(Exception $exc) { }
        return $this->handleAlert('Rol no registrado.', false);
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function habilitar(Request $request, $id)
    {
        if(!is_numeric($id)) {
            return $this->handleAlert('Rol no valido');
        }
        $rol = WbSeguriRoles::find($id);
        if($rol == null) {
            return $this->handleAlert('Rol no encontrado');
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($rol->fk_id_project_Company != $proyecto) {
            return $this->handleAlert('Rol no valido');
        }
        try {
            $rol->estado = 1;
            if($rol->save() ){
                return $this->handleResponse($request, [], 'Rol desbloqueado');
            }
        } catch (Exception $e) { }
        return $this->handleAlert('EL rol no se pudo desbloquear');
    }

    public function delete(Request $request, $id)
    {
        if(!is_numeric($id)) {
            return $this->handleAlert('Rol no valido');
        }
        $rol = WbSeguriRoles::find($id);
        if($rol == null) {
            return $this->handleAlert('Rol no encontrado');
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($rol->fk_id_project_Company != $proyecto) {
            return $this->handleAlert('Rol no valido');
        }
        try {
            $rol->estado = 0;
            if($rol->save() ){
                return $this->handleResponse($request, [], 'Rol bloqueado');
            }
        } catch (Exception $e) { }
        return $this->handleAlert('EL rol no se pudo bloquear');
    }

    public function get(Request $request)
    {
        $consulta = WbSeguriRoles::orderby('nombreRol');
        $consulta = $this->filtrarPorProyecto($request,$consulta)->get();
        return $this->handleResponse($request, $this->wbSeguriRolesToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {

    }
}
