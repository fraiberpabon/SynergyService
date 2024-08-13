<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbEstructFirmas;
use Illuminate\Http\Request;

class WbEstructFirmasController extends BaseController implements  Vervos
{
    //
    public function post(Request $req)
    {
        if (!$req->json()->has('estructPerfil')) {
            return $this->handleAlert(__("messages.falta_campo_estructura_de_perfil"));
        }
        if (!$req->json()->has('area')) {
            return $this->handleAlert(__("messages.falta_campo_area"));
        }
        if (!$req->json()->has('firma')) {
            return $this->handleAlert(__("messages.falta_campo_firma"));
        }
        $modelo = new WbEstructFirmas;
        $modelo->fk_estruc_perfil = $req->estructPerfil;
        $modelo->area = $req->area;
        $modelo->firma = $req->firma;
        $modelo->estado = 1;
        try {
            if ($modelo->save()){
                return $this->handleResponse($req, [], __("messages.perfil_de_firma_creado"));
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
        $consulta = WbEstructFirmas::select();
        if ($request->has('id')) {
            $consulta = $consulta->where('fk_estruc_perfil', $request->id);
        }
        $consulta = $consulta->get();
        return $this->handleResponse($request, $this->wbEstructFirmaToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
