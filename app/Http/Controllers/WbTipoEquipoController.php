<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\wbTipoEquipo;
use Illuminate\Http\Request;

class WbTipoEquipoController extends BaseController implements Vervos
{

    /**
     * @param Request $req
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param Request $request
     */
    public function get(Request $request)
    {
        $consulta = wbTipoEquipo::where('fk_id_project_Company', $this->traitGetProyectoCabecera($request))
            ->where('estado', 1)
            ->get();
        return $this->handleResponse($request, $this->tipoEquipoToArray($consulta), __('messages.consultado'));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
