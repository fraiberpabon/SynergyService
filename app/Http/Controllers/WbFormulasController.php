<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbFormulaCentroProduccion;
use App\Models\WbFormulaLista;
use App\Models\WbMaterialFormula;
use Exception;
use Illuminate\Http\Request;

class WbFormulasController extends BaseController implements Vervos
{
    public function post(Request $req) {
    }

    public function update(Request $req, $id) {
    }

    /**
     * @param $id
     * @return void
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function get(Request $request)
    {
        // TODO: Implement get() method.
        $query = WbFormulaLista::select(
            'id_formula_lista as identificador',
            'Nombre',
            'formulaDescripcion',
            'unidadMedida',
            'fk_id_project_Company'
            )
        ->where('Estado', 'A');

        $query = $this->filtrar($request, $query)->orderBy('id_formula_lista', 'DESC')->get();

        return $this->handleResponse($request, $this->WbFormulasToArray($query), __('messages.consultado'));
    }

    public function getComposicion(Request $request)
    {
        // TODO: Implement get() method.
        $query = WbMaterialFormula::select(
            'id_material_formula as identificador',
            'fk_formula_CentroProduccion',
            'fk_material_CentroProduccion',
            'Porcentaje',
            'fk_codigoFormulaCdp',
            'fk_id_project_Company'
            )
        ->where('Estado', 'A')
        ->whereNotNull('fk_codigoFormulaCdp');

        $query = $this->filtrar($request, $query)->orderBy('id_material_formula', 'DESC')->get();

        return $this->handleResponse($request, $this->WbFormulasComposicionToArray($query), __('messages.consultado'));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
