<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Planta;
use Illuminate\Http\Request;

class PlantaController extends BaseController implements Vervos
{
    //
    /**
     * @param Request $req
     * @return void
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
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
     * @return mixed
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {
        $consulta = Planta::select();
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->plantaToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
