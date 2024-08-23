<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\CnfCostCenter;
use App\Models\Compania;
use App\Models\location;
use App\Models\Planta;
use App\Models\UsuPlanta;
use Exception;
use Illuminate\Http\Request;

class UsuPlantaController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {
    }

    public function getActivos(Request $request)
    {
        $consulta = UsuPlanta::select(
            'usuPlanta.*',
        )->where('estado', 1);
        $consulta = $this->filtrarPorProyecto($request, $consulta)->get();
        return $this->handleResponse($request, $this->usuPlantaToArraySimplificado($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function find($id)
    {
        return UsuPlanta::find($id);
    }

    public function findActive($id)
    {
        return UsuPlanta::where('id_plata', $id)->where('estado', 1)->first();
    }
}
