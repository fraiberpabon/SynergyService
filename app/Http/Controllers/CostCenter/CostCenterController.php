<?php

namespace App\Http\Controllers\CostCenter;

use App\Http\interfaces\Vervos;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Models\WbCostos\WbCostos;

class CostCenterController extends BaseController implements Vervos
{
    /**
     * @param Request $req
     */
    public function post(Request $req) {}

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id) {}

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id) {}

    /**
     * @param Request $request
     */
    public function get(Request $request) {}

    public function getPorProyecto(Request $request, $proyecto) {}


    /**
     * Funcion para obtener los centros de costo
     */
    public function getCostCenterMobile(Request $request)
    {
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $query = WbCostos::where('fk_id_project_Company', $proyecto)
                ->with('compania', 'usuario')
                ->orderBy('id', 'desc');

            $result = $query->get();
            return $this->handleResponse(
                $request,
                $this->CentrosCostoToArray($result),
                __('messages.consultado')
            );
        } catch (Exception $e) {
            Log::error('error al obtener centros de costo mobile ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }
}
