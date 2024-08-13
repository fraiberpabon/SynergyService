<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\usuarios_M;
use App\Models\WbFormulaCapa;
use App\Models\WbFormulaLista;
use App\Models\WbTipoCapa;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbFormulaCapaController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('tipoCapa')) {
            return $this->handleAlert('Falta campo tipoCapa.', false);
        }
        if(!$req->json()->has('formulaLista')) {
            return $this->handleAlert('Falta campo formulaLista.', false);
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.', false);
        }
        if($req->validate([
            'tipoCapa' => 'required',
            'formulaLista' => 'required',
            'estado' => 'required',
        ])) {
            if(WbTipoCapa::find($req->tipoCapa) == null) {
                return $this->handleAlert('Tipo de capa no encontrado.', false);
            }
            if(WbFormulaLista::find($req->formulaLista) == null) {
                return $this->handleAlert('Formula lista no encontrado.', false);
            }
            if(WbFormulaCapa::where('fk_id_tipo_capa', $req->tipoCapa)
            ->where('fk_id_formula_lista', $req->formulaLista)
            ->where('Estado', 'A')
            ->get()->count() > 0) {
                return $this->handleAlert('Esta formula de capa ya se encuentra registrada.', false);
            }
            $modeloRegistrar = new WbFormulaCapa;
            $modeloRegistrar->fk_id_tipo_capa = $req->tipoCapa;
            $modeloRegistrar->fk_id_formula_lista = $req->formulaLista;
            $modeloRegistrar->Estado = $req->estado;
            $modeloRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    $modeloRegistrar->id_formula_capa = $modeloRegistrar->latest('id_formula_capa')->first()->id_formula_capa;
                    return $this->handleResponse($req, $modeloRegistrar, 'Formula capa registrada.');
                }
            } catch(Exception $exc) { }
            return $this->handleAlert('Formula capa no registrada.', false);
        }
    }

    public function registrar($modelo) {
        try {
            if($modelo->save()) {
                return $modelo;
            }
        } catch(Exception $exc) { }
        return null;
    }

    public function update(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Formula capa no valida.');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if(!$req->json()->has('userCreator')) {
            return $this->handleAlert('Falta campo userCreator.');
        }
        if($req->validate([
            'estado' => 'string|max:1',
            'userCreator' => 'numeric'
        ])) {
            $modeloModificar = WbFormulaCapa::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert('Formula capa no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Formula capa no valido.');
            }
            if(usuarios_M::find($req->userCreator) == null) {
                return $this->handleAlert('Usuario no encontrado.');
            }
            $modeloModificar->Estado = $req->estado;
            $modeloModificar->userCreator = $req->userCreator;
            try {
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Formula capa modificada.');
                }
            } catch(Exception $exc) {}
            return $this->handleAlert('Formula capa no modificada.');
        }
    }

    public function getActivosPorTipoCapa(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Formula lista no valida.', false);
        }
        $consulta = WbFormulaCapa::select(
            'id_formula_capa as identificador',
            'fk_id_tipo_capa',
            'fk_id_formula_lista',
            'Wb_Formula_Capa.Estado',
            'Wb_Formula_Capa.dateCreate',
            'Wb_Formula_Capa.userCreator',
            'Wb_Formula_Lista.Nombre',
            'unidadMedida'
        )
        ->leftJoin('Wb_Formula_Lista', 'Wb_Formula_Lista.id_formula_lista',  DB::raw("Wb_Formula_Capa.fk_id_formula_lista and Wb_Formula_Lista.estado = 'A'"))
        ->where('Wb_Formula_Capa.Estado', 'A')
        ->where('fk_id_formula_lista', $id);
        $consulta = $this->filtrar($req, $consulta, 'Wb_Formula_Capa')->get();
        return $this->handleResponse($req, $this->wbFormulaCapaToArray($consulta), __("messages.consultado"));
    }

    public function getConTipoCapaPorFormulaLista(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Formula lista no valida.', false);
        }
        $tiposCapa = WbTipoCapa::all();
        $consulta = WbFormulaCapa::select('*')
        ->where('Estado', 'A')
        ->where('fk_id_formula_lista', $id);
        $consulta = $this->filtrar($req, $consulta, 'Wb_Formula_Capa')->get();
        foreach ($consulta as $item) {
            $this->setTipoCapaById($item, $tiposCapa);
        }
        return $this->handleResponse($req, $this->wbFormulaCapaToArray($consulta), __("messages.consultado"));
    }

    public function getPorTipoCapa(Request $req, $tipoCapa)
    {

        if (strcmp($tipoCapa, 'all') == 0) {
            $consulta = WbFormulaCapa::select(
                'Wb_Formula_Lista.Nombre as nombre',
                'unidadMedida',
                'fk_id_formula_lista'
            )->leftJoin('Wb_Formula_Lista', 'Wb_Formula_Lista.id_formula_lista', 'Wb_Formula_Capa.fk_id_formula_lista')
                ->where('Wb_Formula_Capa.Estado', 'A')
                ->groupBy('Wb_Formula_Lista.Nombre')
                ->groupBy('Wb_Formula_Lista.unidadMedida')
                ->groupBy('Wb_Formula_Capa.fk_id_formula_lista');
        } else {
            $consulta = WbFormulaCapa::select(
                'Wb_Formula_Capa.id_formula_capa as identificador',
                'Wb_Formula_Capa.fk_id_tipo_capa as tipoCapa',
                'Wb_Formula_Capa.fk_id_formula_lista as formulalista',
                'Wb_Formula_Capa.Estado as estado',
                'Wb_Formula_Capa.dateCreate as fechaCreacion',
                'Wb_Formula_Capa.userCreator as usuario',
                'Nombre',
                'unidadMedida'
            )
                ->leftJoin('Wb_Formula_Lista', 'Wb_Formula_Lista.id_formula_lista', DB::raw("Wb_Formula_Capa.fk_id_formula_lista and Wb_Formula_Lista.estado='A'"))
                ->where('Wb_Formula_Capa.Estado', 'A')
                ->where('fk_id_tipo_capa', $tipoCapa);
        }
        try {
            $consulta = $this->filtrar($req, $consulta, 'Wb_Formula_Capa')->get();
            return $this->handleResponse($req, $consulta, __('messages.consultado'));
        } catch (Exception $exc) {
            var_dump($exc);
        }
    }

    public function inHabilitar(Request $request, $id) {
        if (!is_numeric($id)) {
            return $this->handleAlert(__("messages.formula_capa_no_valido"));
        }
        $modelo = WbFormulaCapa::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.formula_capa_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.formula_capa_no_valido"));
        }
        $modelo->Estado = 'I';
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.formula_capa_inhabilitado"));
            }
        } catch (Exception $exc){}
        return $this->handleAlert(__("messages.formula_capa_no_inhabilitado"));
    }

    public function habilitar(Request $request, $id) {
        if (!is_numeric($id)) {
            __("messages.formula_capa_no_valido");
        }
        $modelo = WbFormulaCapa::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.formula_capa_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.formula_capa_no_valido"));
        }
        $modelo->Estado = 'A';
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.formula_capa_habilitado"));
            }
        } catch (Exception $exc){}
        return $this->handleAlert(__("messages.formula_capa_no_habilitado"));
    }

    public function setTipoCapaById($formulaCapa, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($formulaCapa->fk_id_tipo_capa == $array[$i]->id_tipo_capa) {
                $reescribir = $this->wbTipoDeCapaToModel($array[$i]);
                $formulaCapa->objectTipoCapa = $reescribir;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.
        $tiposCapa = WbTipoCapa::all();
        $consulta = WbFormulaCapa::select('*');
        if ($request->has('estado')) {
            $consulta = $consulta->where('Estado', $request->estado);
        }
        if ($request->has('formula_lista')) {
            $consulta = $consulta->where('fk_id_formula_lista', $request->formula_lista);
        }
        $consulta = $this->filtrar($request, $consulta, 'Wb_Formula_Capa')->get();
        foreach ($consulta as $item) {
            $this->setTipoCapaById($item, $tiposCapa);
        }
        return $this->handleResponse($request, $this->wbFormulaCapaToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function getFormulaCapa(Request $request,$id){
        //se consultan los materiales autorizados
        $consulta=WbFormulaCapa::with('formula')->where('estado','A');
        //variable que guarda el estado de no aprobado
        $no=1;
        //se filtra por proyecto
        $consulta = $this->filtrar($request, $consulta, 'Wb_Formula_Capa')->get();

        $permitidas=$consulta;
        if ($id!='all') {
            $permitidas=$permitidas->where('fk_id_tipo_capa',$id);
            $no=0;
        }

        //se extrae la lista de materiales sin autorizacion
        $nopermitidos=$consulta->whereNotIn('formula.id_formula_lista',$permitidas->pluck('formula.id_formula_lista'));
        //se crea la colleccion que va a rtener los dos datos.
        //se formatean los datos aprobados
        $respuesta=collect($this->WbFormulaAutorizadoToArray($permitidas->pluck('formula')->sortBy('Nombre'),1));

        //se formatean los no aprobados
        $respuesta=$respuesta->merge($this->WbFormulaAutorizadoToArray($nopermitidos->pluck('formula')->sortBy('Nombre'),$no));

        return $this->handleResponse($request,$respuesta->unique('identificador')->values(), __('messages.consultado'));
    }
}
