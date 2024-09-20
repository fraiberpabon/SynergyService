<?php

namespace App\Http\Controllers\EquiposLiquidacion;

use App\Http\Controllers\BaseController;
use App\Http\interfaces\Vervos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class WbEquiposLiquidacionDetalleController extends BaseController implements Vervos
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {
        // TODO: Implement post() method
    }

    /**
     * @param Request $request
     * @param $id
     * @return void
     */
    public function update(Request $request, $id)
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
        // TODO: Implement get() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
