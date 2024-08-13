<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\wbHallazgo;
use Illuminate\Http\Request;

class WbHallazgoController extends BaseController implements Vervos
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
        // TODO: Implement get() method.
        $consulta = wbHallazgo::select();
        if ($request->query->has('estado')) {
            $consulta = $consulta->where('estado', $request->estado);
        } else {
            $consulta = $consulta->where('estado', 1);
        }
        //$consulta = $this->filtrar($request, $consulta)->get();
        $consulta = $this->filtrarPorProyecto($request, $consulta)->get();
        return $this->handleResponse($request, $this->hallazgoToArray($consulta), __('message.consultado'));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
