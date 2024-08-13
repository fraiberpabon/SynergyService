<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\SyncCostCode;
use Illuminate\Http\Request;

class SyncCostDescController extends BaseController implements Vervos
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

    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function getByActivoFrente(Request $request, $frente){
        //valida si el usuario existe
        $consulta = SyncCostCode::select(
            'CostCode.costcode',
            'CostCode.costdesc'
        )->leftJoin('TimeScanSI.dbo.JobCostCode as jc', 'jc.CostCode', 'CostCode.CostCode')
            ->where('status', 'A')
            ->where('jc.JobID', $frente)
            ->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

}
