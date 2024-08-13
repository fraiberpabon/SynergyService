<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\estruc_tipos;
use App\Models\EstrucFormula;
use App\Models\EstructuraTipoElemento;
use App\Models\Formula;
use Exception;
use Illuminate\Http\Request;

class EstrucFormulaController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('estructura')) {
            return $this->handleAlert('Falta campo tipo de estructura.');
        }
        if(!$req->json()->has('elemento')) {
            return $this->handleAlert('Falta campo elemento.');
        }
        if(!$req->json()->has('formula')) {
            return $this->handleAlert('Falta campo formula.');
        }
        if($req->validate([
            'estructura' => 'numeric',
            'elemento' => 'numeric',
            'formula' => 'numeric',
        ])) {
            if(EstructuraTipoElemento::find($req->elemento) == null) {
                return $this->handleAlert('Elemento no encontrada.');
            }
            if(estruc_tipos::find($req->estructura) == null) {
                return $this->handleAlert('Tipo de estructura no encontrado.');
            }
            if(Formula::find($req->formula) == null) {
                return $this->handleAlert('Formula no encotrada.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if(EstrucFormula::where('fk_tipo_estructura', $req->estructura)->where('fk_elemento',$req->elemento)->where('estado', 1)->where('fk_formula', $req->formula)->where('fk_id_project_Company', $proyecto)->first() != null) {
                return $this->handleAlert('Ya existe esta asignacion de formula.');
            }
            $modeloRegistrar = new EstrucFormula;
            $modeloRegistrar->fk_tipo_estructura = $req->estructura;
            $modeloRegistrar->fk_elemento = $req->elemento;
            $modeloRegistrar->fk_formula = $req->formula;
            $modeloRegistrar->estado = 1;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    $modeloRegistrar->id = $modeloRegistrar->latest('id')->first()->id;
                    return $this->handleResponse($req, $modeloRegistrar, 'Estruc formula registrada.');
                }
            } catch(Exception $exc) {}
        }
    }

    /**
     * @param Request $req
     * @param $id
     * @return void
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $id)
    { }

    public function deletePr(Request $request, $id)
    {
        // TODO: Implement delete() method.
        if (!is_numeric($id)) {
            return $this->handleAlert('Asignacion de formula no valida');
        }
        $modelo = EstrucFormula::find($id);
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert('Asignacion de formula no valida');
        }
        try {
            if ($modelo->delete()) {
                return $this->handleResponse($request, [], 'Asignacion de formula eliminada');
            }
        } catch (Exception $exc) {}
        return $this->handleAlert('Asignacion no eliminada');
    }

    public function get(Request $request)
    {
        $consulta = EstrucFormula::select(
            'Estruc_formula.id as identificador',
            'Estruc_formula.fk_tipo_estructura as identificadorTipoEstructura',
            'Estruc_tipos.TIPO_DE_ESTRUCTURA as tipoEstructura',
            'Estruc_formula.fk_elemento as identificadorElemento',
            'Estruc_tipo_elemento.Elemento as elemento',
            'Estruc_formula.fk_formula as identificadorFormula',
            'Formula.formula as formula',
            'Formula.Resistencia as resistencia',
            'Formula.Dmx as dmx',
            'Formula.Relacion as relacion',
            'TipoMezcla.Tipo as tipo',
        )
        ->leftjoin('Estruc_tipos', 'Estruc_tipos.id', '=', 'Estruc_formula.fk_tipo_estructura')
        ->leftjoin('Estruc_tipo_elemento', 'Estruc_tipo_elemento.id', '=', 'Estruc_formula.fk_elemento')
        ->leftjoin('Formula', 'Formula.id', 'Estruc_formula.fk_formula')
        ->leftjoin('TipoMezcla', 'TipoMezcla.Id', 'Formula.fk_tipoMezcla')
        ->where('Estruc_formula.estado', '1');
        $consulta = $this->filtrarPorProyecto($request, $consulta, 'Estruc_formula')->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    public function getParaSync(Request $request)
    {
        $consulta = EstrucFormula::select(
            'Estruc_formula.id',
            'tipo.TIPO_DE_ESTRUCTURA as Estructura',
            'ele.Elemento',
            'mez.Tipo as Mezcla',
            'form.formula as Formula',
            'form.Resistencia',
            'form.Dmx',
            'form.Relacion'
        )
            ->leftjoin('Estruc_tipo_elemento as ele',  'ele.id', 'Estruc_formula.fk_elemento')
            ->leftjoin('Estruc_tipos as tipo',  'tipo.id', 'Estruc_formula.fk_tipo_estructura')
            ->leftjoin('Formula as form',  'form.id', 'Estruc_formula.fk_formula')
            ->leftjoin('TipoMezcla as mez',  'mez.id', 'form.fk_tipoMezcla')
            ->where('form.estado', '1')
            ->where('Estruc_formula.estado', '1');
        $consulta = $this->filtrar($request, $consulta, 'Estruc_formula')->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
