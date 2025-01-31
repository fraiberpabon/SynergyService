<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbAsfaltFormula;
use App\Models\WbFormulaCentroProduccion;
use App\Models\WbFormulaLista;
use App\Models\WbMaterialFormula;
use Exception;
use Illuminate\Http\Request;
use DB;

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

    public function getV2(Request $request)
    {
        // TODO: Implement get() method.
        $mat = WbFormulaLista::select(
            'id_formula_lista as identificador',
            DB::raw("'M' as tipo"),
            'Nombre',
            'formulaDescripcion',
            'unidadMedida',
            'fk_id_project_Company'
            )
        ->where('Estado', 'A');

        $mat = $this->filtrar($request, $mat)->orderBy('id_formula_lista', 'DESC')->get();

        $asf = WbAsfaltFormula::select(
            'id_asfal_formula as identificador',
            DB::raw("'A' as tipo"),
            'asfalt_formula as Nombre',
            DB::raw("'Tonelada' as unidadMedida"),
            'fk_id_project_Company'
        )
        ->where('estado', 1);

        $asf = $this->filtrar($request, $asf)->orderBy('id_asfal_formula', 'DESC')->get();

        $query = $mat->concat($asf);

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
