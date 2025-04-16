<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbConfiguraciones;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbConfiguracionesController extends BaseController implements Vervos
{
    /**
     * Inserta un registro de area a la base de datos
     * @param Request $req
     * @return JsonResponse|void
     */
    public function post(Request $req) {}

    /**
     * Elimina un area por id
     * @param $id
     * @return JsonResponse
     */
    public function delete(Request $request, $id) {}

    public function bloquear(Request $request, $id) {}

    public function desbloquear(Request $request, $id) {}

    /**
     * Consulta de todas las areas
     * @return JsonResponse
     */
    public function get(Request $req)
    {
        $result = collect([]);

        $query = WbConfiguraciones::select(
            'transporte_max_peso',
            'transporte_min_peso',
            'transporte_usar_equipo_peso',
            'max_km'
        );

        $query = $this->filtrarPorProyecto($req, $query)->first();
        if ($query != null) {
            //$query->porcentaje_concreto = number_format($query->porcentaje_concreto, 2, '.', ',');

            $result = collect($query->toArray())
                ->map(function ($value, $key) {
                    return [
                        'config_name' => $key,
                        'config_value' => $value,
                    ];
                })->values();
        }

        return $this->handleResponse($req, $result, __("messages.consultado"));
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function getPorProyecto(Request $request, $proyecto) {}
}
