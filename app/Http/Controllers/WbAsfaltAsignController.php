<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\UsuPlanta;
use App\Models\WbAsfaltAsign;
use App\Models\WbAsfaltFormula;
use Illuminate\Http\Request;

class WbAsfaltAsignController extends BaseController implements Vervos
{
    //
    public function post(Request $req)
    {
        if (!$req->json()->has('formula')) {
            return $this->handleAlert(__("messages.campo_formula_no_encontrado"));
        }
        if (!$req->json()->has('planta')) {
            return $this->handleAlert(__("messages.campo_planta_no_encontrado"));
        }
        $buscar = WbAsfaltAsign::where('fk_asfal_formula', $req->formula)->where('fk_planta', $req->planta);
        $buscar = $this->filtrarPorProyecto($req, $buscar)->first();
        if ($buscar != null) {
            return $this->handleAlert(__("messages.ya_se_asgino_esta_formula_asfalto_a_la_planta"));
        }
        $modelo = new WbAsfaltAsign;
        $modelo->fk_asfal_formula = $req->formula;
        $modelo->fk_planta = $req->planta;
        $modelo = $this->traitSetProyectoYCompania($req, $modelo);
        try {
            if ($modelo->save()) {
                return $this->handleResponse($req, [], __("messages.formula_asfalto_asignado"));
            }
        }catch (\Exception $exc) {}
        return $this->handleAlert(__("messages.formula_asfalto_no_asignado"));
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
        $consulta = WbAsfaltAsign::select();
        if ($request->query('estado') != null) {
            $consulta = $consulta->where('estado', 1);
        }
        /**
         * Filtrar por proyecto y compañia
         */
        $consulta = $this->filtrar($request, $consulta)->get();
        $formulas = WbAsfaltFormula::all();
        $usuPlantas = UsuPlanta::all();
        foreach ($consulta as $item) {
            $this->setFormula($item, $formulas);
            $this->setPlanta($item, $usuPlantas);
        }
        return $this->handleResponse($request, $this->wbAsfaltFormulaAsgignToArray($consulta), __("messages.consultado"));
    }

    public function getActivos(Request $request)
    {
        $consulta = WbAsfaltAsign::select(
            'id_asfalt_asig',
            'ASF.asfalt_formula',
            'PLA.NombrePlanta',
            'fk_asfal_formula',
            'Wb_Asfalt_Asig.fk_planta',
            'Wb_Asfalt_Asig.estado',
        )
            ->leftJoin('Wb_Asfal_Formula as ASF', 'ASF.id_asfal_formula', 'Wb_Asfalt_Asig.fk_asfal_formula')
            ->leftJoin('usuPlanta as PLA', 'PLA.id_plata', 'Wb_Asfalt_Asig.fk_planta')
            ->where('Wb_Asfalt_Asig.estado', 1);
        /**
         * Filtrar por proyecto y compañia
         */
        $consulta = $this->filtrar($request, $consulta, 'Wb_Asfalt_Asig')->get();
        return $this->handleResponse($request, $this->wbAsfaltFormulaAsgignToArray2($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function activar(Request $request, $id) {
        if (!is_numeric($id)) {
            return $this->handleAlert(__("messages.asignacion_de_formula_a_planta_no_valido"));
        }
        $modelo = WbAsfaltAsign::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.asignacion_de_formula_a_planta_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($proyecto != $modelo->fk_id_project_Company) {
            return $this->handleAlert(__("messages.asignacion_de_formula_a_planta_no_valido"));
        }
        $modelo->estado = 1;
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.asignacion_de_formula_a_planta_activado"));
            }
        } catch (\Exception $exc) {}
        return $this->handleAlert(__("messages.asignacion_de_formula_a_planta_no_activado"));
    }

    public function desActivar(Request $request, $id) {
        if (!is_numeric($id)) {
            return $this->handleAlert(__("messages.asignacion_de_formula_a_planta_no_valido"));
        }
        $modelo = WbAsfaltAsign::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.asignacion_de_formula_a_planta_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($proyecto != $modelo->fk_id_project_Company) {
            return $this->handleAlert(__("messages.asignacion_de_formula_a_planta_no_valido"));
        }
        $modelo->estado = 0;
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.asignacion_de_formula_a_planta_desactivado"));
            }
        } catch (\Exception $exc) {}
        return $this->handleAlert(__("messages.asignacion_de_formula_a_planta_no_desactivado"));
    }

    private function setFormula($modelo, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($modelo->fk_asfal_formula == $array[$i]->id_asfal_formula) {
                $reescribir= $this->wbAsfaltFormulaToModel($array[$i]);
                $modelo->objectFormula = $reescribir;
                break;
            }
        }
    }
    private function setPlanta($modelo, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($modelo->fk_planta == $array[$i]->id_plata) {
                $reescribir= $this->usuPlantaToModel($array[$i]);
                $modelo->objectPlanta = $reescribir;
                break;
            }
        }
    }
}
