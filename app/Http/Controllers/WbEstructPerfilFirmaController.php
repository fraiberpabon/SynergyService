<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbEstructPerfilFirma;
use Illuminate\Http\Request;

class WbEstructPerfilFirmaController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
        if (!$req->json()->has('nombre')) {
            return $this->handleAlert(__("messages.falta_campo_nombre"));
        }
        if (!$req->json()->has('descripcion')) {
            return $this->handleAlert(__("messages.falta_campo_descripcion"));
        }
        $modelo = new WbEstructPerfilFirma;
        $modelo->nombre_perfil = $req->nombre;
        $modelo->descripcion = $req->descripcion;
        $modelo->estado = 1;
        try {
            if ($modelo->save()) {
                $modelo->id_estruc_perfil = $modelo->latest('id_estruc_perfil')->first()->id_estruc_perfil;
                return $this->handleResponse($req, $modelo->id_estruc_perfil, __("messages.perfil_de_firma_creado"));
            }
        } catch (\Exception $exc){}
        return $this->handleAlert(__("messages.perfil_de_firma_no_creado"));
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

    }

    public function getActivos(Request $request){
        $consulta = WbEstructPerfilFirma::where('estado', 1);
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->wbEstructPerfilFrimasToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
