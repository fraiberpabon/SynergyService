<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\wbTipoCarril;
use Illuminate\Http\Request;

class WbTipoCarrilController extends BaseController implements Vervos
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
        $consulta = wbTipoCarril::all();
        return $this->handleResponse($request, $this->tipoCarrilToArray($consulta), '');
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
