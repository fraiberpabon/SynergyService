<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\UsuPlanta;
use App\Models\WbFormulaCapa;
use App\Models\WbFormulaCentroProduccion;
use App\Models\WbFormulaLista;
use App\Models\WbTipoCapa;
use Exception;
use Illuminate\Http\Request;

class WbFormulaListaController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('nombre')) {
            return $this->handleAlert(__("messages.falta_campo_nombre"));
        }
        if(!$req->json()->has('formulaDescripcion')) {
            return $this->handleAlert(__("messages.falta_campo_descripcion"));
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert(__("messages.falta_campo_estado"));
        }
        if(!$req->json()->has('unidadMedida')) {
            return $this->handleAlert(__("messages.falta_campo_unidad_de_medida"));
        }
        if(!$req->json()->has('capas')) {
            return $this->handleAlert(__("messages.falta_campo_unidad_de_medida"));
        }
        if(!$req->json()->has('centroProduccion')) {
            return $this->handleAlert(__("messages.falta_campo_unidad_de_medida"));
        }
        if (!is_array($req->capas)) {
            return $this->handleAlert(__("messages.capas_no_validas"));
        }
        if($req->validate([
            'nombre' => 'string|max:20',
            'formulaDescripcion' => 'string|max:20',
            'estado' => 'string|max:1',
            'unidadMedida' => 'string|max:50',
        ])) {
            $proyecto = $this->traitGetProyectoCabecera($req);
            if(WbFormulaLista::where('Nombre', $req->nombre)->where('fk_id_project_Company', $proyecto)->first() != null) {
                return $this->handleAlert(__("messages.este_nombre_esta_siendo_utilizado_por_otra_formula_lista"));
            }
            $modeloRegistrar = new WbFormulaLista;
            $modeloRegistrar->Nombre = $req->nombre;
            $modeloRegistrar->formulaDescripcion = $req->formulaDescripcion;
            $modeloRegistrar->Estado = $req->estado;
            $modeloRegistrar->unidadMedida = $req->unidadMedida;
            $modeloRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    $modeloRegistrar->id_formula_lista = $modeloRegistrar->latest('id_formula_lista')->first()->id_formula_lista;
                    //registrar formula capa
                    $formulaCapaCentroProduccion = new WbFormulaCentroProduccionController();
                    $formulaCapaController = new WbFormulaCapaController();
                    foreach ($req->capas as $capa) {
                        $formulaCapa = new WbFormulaCapa;
                        $formulaCapa->fk_id_tipo_capa = $capa;
                        $formulaCapa->fk_id_formula_lista = $modeloRegistrar->id_formula_lista;
                        $formulaCapa->Estado = 'A';
                        $formulaCapa->userCreator = $modeloRegistrar->userCreator;
                        $formulaCapa = $this->traitSetProyectoYCompania($req, $formulaCapa);
                        $formulaCapa = $formulaCapaController->registrar($formulaCapa);
                    }
                    $fecha = $this->traitGetDateNowFormatFull();
                    $milisegundos = round(substr($fecha,13, 15));
                    $codigo = $modeloRegistrar->id_formula_lista
                        .substr($modeloRegistrar->Nombre,0, 2)
                        .substr($modeloRegistrar->formulaDescripcion, 0, 2)
                        .$fecha
                        .$milisegundos
                        .$req->centroProduccion;
                    $formulaCentroProduccion = new WbFormulaCentroProduccion;
                    $formulaCentroProduccion->fk_id_formula_lista = $modeloRegistrar->id_formula_lista;
                    $formulaCentroProduccion->fk_id_planta = $req->centroProduccion;
                    $formulaCentroProduccion->Estado = 'A';
                    $formulaCentroProduccion->userCreator = $modeloRegistrar->userCreator;
                    $formulaCentroProduccion->codigoFormulaCdp = $codigo;
                    $formulaCentroProduccion = $this->traitSetProyectoYCompania($req, $formulaCentroProduccion);
                    $formulaCentroProduccion = $formulaCapaCentroProduccion->registrar($formulaCentroProduccion);
                    if ($formulaCentroProduccion != null) {
                        $formulaCentroProduccion->id_formula_centroProduccion = $formulaCentroProduccion->latest('id_formula_centroProduccion')->first()->id_formula_centroProduccion;
                    }
                    return $this->handleResponse($req, [
                        'codigo'=>$formulaCentroProduccion->codigoFormulaCdp,
                        'id'=>$formulaCentroProduccion->id_formula_centroProduccion,
                    ], __("messages.formula_lista_registrado"));
                }
            } catch(Exception $exc) { }
            return $this->handleAlert(__("messages.formula_lista_no_registrado"));
        }
    }

    public function update(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert(__("messages.formula_lista_no_valido"));
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert(__("messages.falta_campo_estado"));
        }
        if($req->validate([
            'estado' => 'string|max:1',
            'userCreator' => 'numeric'
            ])) {
            $modeloModificar = WbFormulaLista::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert(__("messages.formula_lista_no_encontrado"));
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert(__("messages.formula_lista_no_valido"));
            }
            $modeloModificar->Estado = $req->estado;
            try {
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, __("messages.formula_lista_modificado"));
                }
            } catch(Exception $exc) {}
            return $this->handleAlert(__("messages.formula_lista_no_modificado"));
        }
    }

    public function inHabilitar(Request $request, $id) {
        if (!is_numeric($id)) {
            return $this->handleAlert(__("messages.formula_lista_no_valido"));
        }
        $modelo = WbFormulaLista::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.formula_lista_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.formula_lista_no_valido"));
        }
        $modelo->estado = 'I';
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.formula_lista_inhabilitado"));
            }
        } catch (Exception $exc){}
        return $this->handleAlert(__("messages.formula_lista_no_inhabilitado"));
    }

    public function habilitar(Request $request, $id) {
        if (!is_numeric($id)) {
            __("messages.formula_lista_no_valido");
        }
        $modelo = WbFormulaLista::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.formula_lista_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.formula_lista_no_valido"));
        }
        $modelo->estado = 'A';
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.formula_lista_habilitado"));
            }
        } catch (Exception $exc){}
        return $this->handleAlert(__("messages.formula_lista_no_habilitado"));
    }

    public function get(Request $request){
        $consulta = WbFormulaLista::select();
        $consulta = $this->filtrar($request, $consulta)->get();
        $controladorFormulaCapa = new WbFormulaCapaController();
        $controladorFormulaCentroProduccion = new WbFormulaCentroProduccionController();
        $tiposCapa = WbTipoCapa::all();
        $ususPlanta = UsuPlanta::all();
        foreach ($consulta as $item) {
            $formulasCapa = WbFormulaCapa::where('Estado', 'A')
            ->where('fk_id_formula_lista', $item->id_formula_lista)
            ->get();
            $formulasCentroProduccion = WbFormulaCentroProduccion::where('Estado', 'A')
            ->where('fk_id_formula_lista', $item->id_formula_lista)
            ->get();
            foreach ($formulasCapa as $formulaCapa) {
                $controladorFormulaCapa->setTipoCapaById($formulaCapa, $tiposCapa);
            }
            foreach ($formulasCentroProduccion as $formulaCentroProduccion) {
                $controladorFormulaCentroProduccion->setUsuPlantaById($formulaCentroProduccion, $ususPlanta);
            }
            $item->listaFormulaCapa = $this->wbFormulaCapaToArray($formulasCapa);
            $item->listaFormulaCentroProduccion = $this->wbFormulaCentroProduccionToArray($formulasCentroProduccion);
        }
        return $this->handleResponse($request, $this->wbFormulaListaToArray($consulta), __("messages.consultado"));
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
        // TODO: Implement getPorProyecto() method.
    }
}
