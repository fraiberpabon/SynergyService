<?php

namespace App\Http\Controllers\EquiposLiquidacion;

use App\Http\Controllers\BaseController;
use App\Http\interfaces\Vervos;
use App\Models\EquiposLiquidacion\WbEquiposLiquidacion;
use Illuminate\Http\Request;
use Validator;
use DB;
use Carbon\Carbon;

class WbEquiposLiquidacionController extends BaseController implements Vervos
{
    //id estados de la tabla estados
    private $estados = array('Liquidado' => 43, 'Error' => 44);
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {
    }

    /**
     * @param Request $request
     * @param $id
     * @return void
     */
    public function update(Request $request, $id)
    {

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



    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function getFechaUltimoCierre(Request $request)
    {
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $liquidacion = WbEquiposLiquidacion::where('fk_id_project_Company', $proyecto)
                ->where('fk_id_estado', $this->estados['Liquidado'])
                ->orWhere('fk_id_estado', $this->estados['Error'])
                ->max(DB::raw("CAST(liq_fecha_final AS DATE)"));
            if ($liquidacion == null) {
                return $this->handleAlert('1999-01-01', true);
            }
            return $this->handleAlert($liquidacion, true);
        } catch (\Throwable $th) {
            return $this->handleAlert($th->getMessage());
        }
    }
}
