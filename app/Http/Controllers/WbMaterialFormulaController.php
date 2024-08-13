<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\UsuPlanta;
use App\Models\WbFormulaCentroProduccion;
use App\Models\WbMaterialCentroProduccion;
use App\Models\WbMaterialFormula;
use App\Models\WbMaterialLista;
use Exception;
use Illuminate\Http\Request;

class WbMaterialFormulaController extends BaseController implements Vervos
{
    public function post(Request $req) {

        if(!$req->json()->has('formulaCentroProduccion')) {
            return $this->handleAlert(__("messages.falta_campo_formula_centro_produccion_porcentaje"));
        }
        if(!$req->json()->has('materialCentroProduccion')) {
            return $this->handleAlert(__("messages.falta_campo_material_centro_produccion"));
        }
        if(!$req->json()->has('porcentaje')) {
            return $this->handleAlert(__("messages.falta_campo_porcentaje"));
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert(__("messages.falta_campo_estado"));
        }
        if(!$req->json()->has('codigoFormula')) {
            return $this->handleAlert(__("messages.falta_campo_codigo_formula"));
        }
        if($req->validate([
            'porcentaje' => 'required|max:50',
            'estado' => 'required|max:1',
            'codigoFormula' => 'required'
        ])) {
            if(WbFormulaCentroProduccion::find($req->formulaCentroProduccion) == null) {
                return $this->handleAlert(__("messages.material_formula_no_encontrado"));
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if(WbMaterialFormula::where('fk_formula_CentroProduccion', $req->formulaCentroProduccion)
            ->where('fk_material_CentroProduccion', $req->materialCentroProduccion)
            ->where('fk_codigoFormulaCdp', $req->codigoFormula)
            ->where('fk_id_project_Company', $proyecto)
            ->first() != null) {
                return $this->handleAlert(__("messages.ya_existe_este_material_formula_registrado_en_el_sistema"));
            }
            $modeloRegistrar = new WbMaterialFormula;
            $modeloRegistrar->fk_formula_CentroProduccion = $req->formulaCentroProduccion;
            $modeloRegistrar->fk_material_CentroProduccion = $req->materialCentroProduccion;
            $modeloRegistrar->Porcentaje = $req->porcentaje;
            $modeloRegistrar->Estado = $req->estado;
            $modeloRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar->fk_codigoFormulaCdp = $req->codigoFormula;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    $modeloRegistrar->id_material_formula = $modeloRegistrar->latest('id_material_formula')->first()->id_material_formula;
                    return $this->handleResponse($req, $modeloRegistrar, __("messages.material_formula_no_encontrado"));
                }
            } catch(Exception $exc) { }
            return $this->handleAlert(__("messages.material_formula_no_registrado"));
        }
    }

    public function update(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert(__("messages.material_formula_no_valido"));
        }
        if (!$req->json()->has('porcentaje')) {
            return $this->handleAlert(__("messages.falta_campo_porcentaje"));
        }
        if (!$req->json()->has('codigoFormula')) {
            return $this->handleAlert(__("messages.falta_campo_codigo_formula"));
        }
        if($req->validate([
            'porcentaje' => 'numeric',
        ])) {
            $modeloModificar = WbMaterialFormula::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert(__("messages.material_formula_no_encontrado"));
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert(__("messages.material_formula_no_valido"));
            }
            //modifico el material formula actual y lo inhabilito
            $modeloModificar->Porcentaje = $req->porcentaje;
            $modeloModificar->Estado = 'I';
            $modeloModificar->userCreator = $this->traitGetIdUsuarioToken($req);
            try {
                if($modeloModificar->save()) {
                    $consulta = WbMaterialFormula::where('Estado', 'A')
                        ->where('fk_formula_CentroProduccion', $req->formulaCentroProduccion)
                        ->first();
                    $modeloRegistrar = new WbMaterialFormula;
                    $modeloRegistrar->fk_formula_CentroProduccion = $req->formulaCentroProduccion;
                    $modeloRegistrar->fk_material_CentroProduccion = $req->materialCentroProduccion;
                    $modeloRegistrar->Porcentaje = $req->porcentaje;
                    $modeloRegistrar->Estado = 'A';
                    $modeloRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
                    $modeloRegistrar->fk_codigoFormulaCdp = $consulta->fk_codigoFormulaCdp;
                    $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
                    try {
                        if($modeloRegistrar->save()) {
                            $modeloRegistrar->id_material_formula = $modeloRegistrar->latest('id_material_formula')->first()->id_material_formula;
                            return $this->handleResponse($req, $consulta->fk_codigoFormulaCdp, __("messages.material_formula_modificado"));
                        }
                    } catch(Exception $exc) { }
                }
            } catch(Exception $exc) {}
            return $this->handleAlert(__("messages.material_formula_no_modificado"));
        }
    }

    public function cambiarEstado(Request $request, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert(__("messages.material_formula_no_valido"));
        }
        if(!$request->json()->has('fk_formula_CentroProduccion')) {
            return $this->handleAlert(__("messages.falta_campo_formula_centro_produccion_porcentaje"));
        }
        if (!$request->json()->has('estado')) {
            return $this->handleAlert(__("messages.falta_campo_estado"));
        }
        if (!($request->estado == 'A' || $request->estado == 'I')) {
            return $this->handleAlert(__("messages.estado_no_valido"));
        }
        $modeloModificar = WbMaterialFormula::find($id);
        if($modeloModificar == null) {
            return $this->handleAlert(__("messages.material_formula_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modeloModificar->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.material_formula_no_valido"));
        }
        //modifico el material formula actual y lo inhabilito
        $modeloModificar->Estado = $request->estado;
        try {
            if($modeloModificar->save()) {
                $consulta = WbMaterialFormula::where('Estado', 'A')
                    ->where('fk_formula_CentroProduccion', $request->fk_formula_CentroProduccion)
                    ->first();
                if ($consulta !== null) {
                    $rest = $consulta->fk_codigoFormulaCdp;
                } else {
                    $consulta = WbFormulaCentroProduccion::where('id_anterior', $request->fk_formula_CentroProduccion)
                        ->where('Estado', 'A')
                        ->orderBy('id_formula_centroProduccion', 'desc')
                        ->first();
                    $rest = $consulta->codigoFormulaCdp;
                }
                return $this->handleResponse($request, [
                    'codigo' => $consulta->codigoFormulaCdp,
                    'id' => $consulta->id_formula_centroProduccion
                ], __("messages.estado_cambiado"));
            }
        } catch(Exception $exc) { }
        return $this->handleResponse($request, [], __("messages.estado_no_cambiado"));
    }

    public function getPorFormulaCdp(Request $req, $id) {
        $consulta = WbMaterialFormula::where('Wb_Material_Formula.Estado', 'A')
        ->where('fk_codigoFormulaCdp', $id)
        ->orderBy('dateCreate', 'desc');
        $consulta = $this->filtrar($req, $consulta)->get();
        $formulasCentroProduccion = WbFormulaCentroProduccion::all();
        $materialesCentroProduccion = WbMaterialCentroProduccion::all();
        $materialesLista = WbMaterialLista::all();
        $ususPlanta = UsuPlanta::all();
        foreach ($consulta as $item) {
            $this->setFormulaCentroProduccionById($item, $formulasCentroProduccion);
            $this->setMaterialCentroProduccionById($item, $materialesCentroProduccion);
            $this->setMaterialListaById($item, $materialesLista);
            $this->setUsuPlantaById($item, $ususPlanta);
        }
        return $this->handleResponse($req, $this->wbMaterialFormulaToArray($consulta), __("messages.consultado"));
    }

    public function setFormulaCentroProduccionById($modelo, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($modelo->fk_formula_CentroProduccion == $array[$i]->id_formula_centroProduccion) {
                $reescribir = $this->wbFormulaCentroProduccionToModel($array[$i]);
                $modelo->objectFormulaCentroProduccion = $reescribir;
                break;
            }
        }
    }

    public function setMaterialCentroProduccionById($modelo, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($modelo->fk_material_CentroProduccion == $array[$i]->id_material_centroProduccion) {
                $reescribir = $this->wbMaterialCentroProduccionToModel($array[$i]);
                $modelo->objectMaterialCentroProduccion = $reescribir;
                break;
            }
        }
    }

    public function setMaterialListaById($modelo, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($modelo->objectMaterialCentroProduccion != null && $modelo["objectMaterialCentroProduccion"]['materialLista'] == $array[$i]->id_material_lista) {
                $reescribir = $this->wbMaterialListaToModel($array[$i]);
                $modelo->objectMaterialLista = $reescribir;
                break;
            }
        }
    }

    public function setUsuPlantaById($modelo, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($modelo->objectMaterialCentroProduccion != null && $modelo['objectMaterialCentroProduccion']['planta'] == $array[$i]->id_plata) {
                $reescribir = $this->usuPlantaToModel($array[$i]);
                $modelo->objectUsuPlanta = $reescribir;
                break;
            }
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

    /**
     * @return void
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
