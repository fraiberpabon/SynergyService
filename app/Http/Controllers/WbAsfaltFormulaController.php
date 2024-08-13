<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbAsfaltFormula;
use Illuminate\Http\Request;

class WbAsfaltFormulaController extends BaseController implements Vervos
{

    public function post(Request $req)
    {
        if($req->validate([
            'formula' => 'string|max:50',
        ])) {
            if (!$req->has('formula')) {
                return $this->handleAlert(__("messages.falta_campo_formula"));
            }
            $buscar = WbAsfaltFormula::where('asfalt_formula', $req->formula);
            $buscar = $this->filtrarPorProyecto($req, $buscar)->first();
            if ($buscar != null) {
                return $this->handleAlert(__("messages.ya_existe_esta_formula_asfalto"));
            }
            $modelo = new WbAsfaltFormula;
            $modelo->asfalt_formula = $req->formula;
            $modelo = $this->traitSetProyectoYCompania($req, $modelo);
            try {
                if ($modelo->save()) {
                    return $this->handleResponse($req, [], __("messages.formula_de_asfalto_registrado"));
                }
            }catch (\Exception $exc) {}
        }
        return $this->handleAlert(__("messages.formula_de_asfalto_no_registrado"));
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
        $consulta = WbAsfaltFormula::select();
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->wbAsfaltFormulaToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
