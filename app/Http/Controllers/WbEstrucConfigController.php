<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbEstrucConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WbEstrucConfigController extends BaseController implements Vervos
{
    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
        if (!$req->json()->has('estrucPerfil')) {
            return $this->handleAlert('Falta campo estructura perfil.');
        }
        if (!$req->json()->has('empresa')) {
            return $this->handleAlert('Falta campo empresa.');
        }
        if (!$req->json()->has('nombreConfig')) {
            return $this->handleAlert('Falta campo nombre configuracion.');
        }
        $validator = Validator::make($req->all(), [
            'estrucPerfil' => 'required|numeric',
            'empresa' => 'required|string',
            'nombreConfig' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->handleAlert("La información recibida no pudo ser enviada al servidor, por favor comuníquese con el área de sistemas");
        }
        $modelo = new WbEstrucConfig;
        $modelo->fk_estruc_perfil = $req->estrucPerfil;
        $modelo->empresa = $req->empresa;
        $modelo->nombre_config = $req->nombreConfig;
        $modelo = $this->traitSetProyectoYCompania($req, $modelo);
        try {
            if ($modelo->save()) {
                return $this->handleResponse($req, [], 'EstrucConfig registrada');
            }
        } catch (\Exception $exc) {}
        return $this->handleAlert("EstrtucConfig no registrada.");
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
     * @return void
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.
    }

    public function getActivos(Request $request) {
        $consulta = WbEstrucConfig::select()->where('estado', 1);
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->wbEstructConfigToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
