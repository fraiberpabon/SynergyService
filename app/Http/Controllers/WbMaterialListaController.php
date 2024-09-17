<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Usuarios\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbMaterialCapa;
use App\Models\WbMaterialCentroProduccion;
use App\Models\Materiales\WbMaterialLista;
use App\Models\Materiales\WbMaterialTipos;
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
        try {
            $consulta = WbMaterialLista::with([
                'tipo_material' => function ($query) {
                    $query->select('id_material_tipo','tipoDescripcion','Compuesto');
                }
            ])
            ->where('Estado', 'A')
            ->select('id_material_lista', 'Nombre', 'Descripcion', 'unidadMedida','fk_id_material_tipo', 'Solicitable', 'fk_id_project_company');
            $consulta = $this->filtrar($request, $consulta)->orderBy('id_material_lista','DESC')->get();

            //return $this->handleResponse($request, $consulta->get(), __("messages.consultado"));
            return $this->handleResponse($request, $this->wbMaterialListaToArray($consulta), __("messages.consultado"));
        } catch (\Throwable $th) {
            //throw $th;
            return $this->handleAlert($th->getMessage(), false);
        }
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
