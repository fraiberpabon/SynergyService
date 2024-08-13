<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Formula;
use App\Models\TipoMezcla;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormulaController extends BaseController implements Vervos
{
    public function post(Request $req){
        if(!$req->json()->has('formula')) {
            return $this->handleAlert(__("messages.falta_campo_formula"));
        }
        if(!$req->json()->has('tipoMezcla')) {
            return $this->handleAlert(__("messages.falta_campo_tipo_mezcla"));
        }
        if(!$req->json()->has('dmx')) {
            return $this->handleAlert(__("messages.falta_campo_dmx"));
        }
        if(!$req->json()->has('resistencia')) {
            return $this->handleAlert(__("messages.falta_campo_recistencia"));
        }
        if($req->validate([
            'formula' => 'string',
            'tipoMezcla' => 'numeric',
            'dmx' => 'string',
            'resistencia' => 'string'
        ])) {
            if(TipoMezcla::find($req->tipoMezcla) == null) {
                return $this->handleAlert(__("messages.formula_registrada"));
            }
            $modeloRegistrar = new Formula;
            $modeloRegistrar->formula = $req->formula;
            $modeloRegistrar->fk_tipoMezcla = $req->tipoMezcla;
            $modeloRegistrar->dmx = $req->dmx;
            $modeloRegistrar->resistencia = $req->resistencia;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    $modeloRegistrar->id = $modeloRegistrar->latest('id')->first()->id;
                    return $this->handleResponse($req, $modeloRegistrar, __("messages.formula_registrada"));
                }
            } catch(Exception $exc){}
            return $this->handleAlert(__("messages.formula_no_registrada"));
        }
    }

    public function inHabilitar(Request $request, $id) {
        if (!is_numeric($id)) {
            return $this->handleAlert(__("messages.formula_no_valida"));
        }
        $modelo = Formula::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.formula_no_encontrada"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.formula_no_valida"));
        }
        $modelo->estado = 0;
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.formula_inhabilitada"));
            }
        } catch (Exception $exc){}
        return $this->handleAlert(__("messages.formula_no_inhabilitada"));
    }

    public function habilitar(Request $request, $id) {
        if (!is_numeric($id)) {
            __("messages.formula_no_valida");
        }
        $modelo = Formula::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.formula_no_encontrada"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.formula_no_valida"));
        }
        $modelo->estado = 1;
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.formula_habilitada"));
            }
        } catch (Exception $exc){}
        return $this->handleAlert(__("messages.formula_no_habilitada"));
    }

    public function delete(Request $request, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Formula no valida.');
        }
        $modelo = Formula::find($id);
        if($modelo == null) {
            return $this->handleAlert('Formula no encontrado.');
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert('Formula no valida.');
        }
        try {
            DB::table('Formula')
                ->where('id', $id)
                ->delete();
            return $this->handleResponse($request, [],'Formula eliminada eliminado.');
        } catch(Exception $exc) {}
        return $this->handleAlert('Formula no eliminado.');
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function get(Request $request)
    {
        $consulta = Formula::select();
        if ($request->estado) {
            $consulta->where('estado', $request->estado);
        }
        $consulta = $this->filtrar($request, $consulta)->get();
        $formulas = TipoMezcla::all();
        foreach ($consulta as $item) {
            $this->setTipoMezclaById($item, $formulas);
        }
        return $this->handleResponse($request, $this->formulaToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {

    }

    private function setTipoMezclaById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_tipoMezcla == $array[$i]->Id) {
                $reescribir = $this->tipoMezclaToModel($array[$i]);
                $estructura->objectTipoMezcla = $reescribir;
                break;
            }
        }
    }
}
