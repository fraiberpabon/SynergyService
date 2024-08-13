<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\CostCode;
use Illuminate\Http\Request;

class CostCodeController extends BaseController implements Vervos
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
        $consulta = CostCode::select();
        if ($request->has('id')) {
            $consulta = $consulta->where('CostCode', $request->id);
        }
        $consulta = $consulta->get();
        return $this->handleResponse($request, $this->costCodeToArray($consulta), __("messages.consultado"));
    }

    public function getActivos(Request $request)
    {
        $consulta = CostCode::where('Status', 'A')->get();
        return $this->handleResponse($request, $this->costCodeToArray($consulta), __("messages.consultado"));
    }


    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
