<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\SyncConfig;
use App\Models\Usuarios\usuarios_M;
use Illuminate\Http\Request;

class SyncConfigController extends BaseController implements Vervos
{
    //
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

    public function get(Request $request)
    {
        $consulta = SyncConfig::select()->get();
        return $this->handleResponse($request, $consulta, 'consultado');
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function getByUser(Request $request, $user){
        $proyecto=$this->traitGetProyectoCabecera($request);
        //valida si el usuario existe
        $usuario=usuarios_M::where('estado','A')->where('usuario',$user)->get();
        //si no existe devuelve error
        if ($usuario->count()<1) {
             return $this->handleAlert('Error al intentar realizar la consulta',false);
        }
        $consulta = SyncConfig::select('dato')->where('para',$user)->where('fk_id_project_Company',$proyecto)->first();
        return $this->handleResponse($request, $consulta, 'consultado');
    }
}
