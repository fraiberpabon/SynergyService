<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbMaterialCapa;
use App\Models\WbMaterialCentroProduccion;
use App\Models\WbMaterialLista;
use App\Models\WbMaterialTipos;
use App\Models\WbTipoCapa;
use Exception;
use Illuminate\Http\Request;

class WbMaterialListaController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
    }

    public function update(Request $req, $id)
    {
    }

    public function get(Request $request)
    {
        $response = WbMaterialLista::where('Estado', 'A');
        $response = $this->filtrar($request, $response)->get();
        if (sizeof($response) == 0) {
            return $this->handleAlert(__("messages.sin_registros_por_mostrar"), false);
        }
        return $this->handleResponse($request, $this->wbMaterialListaToArray($response), __("messages.consultado"));
    }

    /**
     * @param $id
     * @return void
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        return WbMaterialLista::where('estado', 'A')->where('fk_id_project_Company', $proyecto)->get();
    }

}
