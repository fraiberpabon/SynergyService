<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbConfiguraciones;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbConfiguracionesController extends BaseController implements  Vervos
{
    /**
     * Inserta un registro de area a la base de datos
     * @param Request $req
     * @return JsonResponse|void
     */
    public function post(Request $req) {

    }

    /**
     * Elimina un area por id
     * @param $id
     * @return JsonResponse
     */
    public function delete(Request $request, $id) {

    }

    public function bloquear(Request $request, $id) {

    }

    public function desbloquear(Request $request, $id) {

    }

    /**
     * Consulta de todas las areas
     * @return JsonResponse
     */
    public function get(Request $request) {
       $consulta = WbConfiguraciones::select('Wb_configuraciones.*');
        $consulta = $this->filtrar($request, $consulta)->first();
        if ($consulta != null) {
            $consulta->porcentaje_concreto = number_format($consulta->porcentaje_concreto, 2, '.', ',');
        }
       return $this->handleResponse($request, $this->configuracionesToModel($consulta),  __("messages.consultado"));
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {

    }
}