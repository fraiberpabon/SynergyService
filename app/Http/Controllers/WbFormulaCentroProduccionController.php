<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Usuarios\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\WbFormulaCentroProduccion;
use App\Models\WbFormulaLista;
use App\Models\WbCentroProduccionHitos;
use Exception;
use Illuminate\Http\Request;

class WbFormulaCentroProduccionController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
        if (!$req->json()->has('formulaLista')) {
            return $this->handleAlert('Falta campo formulaLista.', false);
        }
        if (!$req->json()->has('planta')) {
            return $this->handleAlert('Falta campo planta.', false);
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.', false);
        }

        if (!$req->json()->has('codigoFormulaCdp')) {
            return $this->handleAlert('Falta campo codigoFormulaCdp.', false);
        }
        if ($req->validate([
            'formulaLista' => 'numeric',
            'planta' => 'numeric',
            'estado' => 'string|max:1',
            'codigoFormulaCdp' => 'string|max:25'
        ])) {
            if (WbFormulaLista::find($req->formulaLista) == null) {
                return $this->handleAlert('Formula lista no encontrado.', false);
            }
            if (UsuPlanta::find($req->planta) == null) {
                return $this->handleAlert('Usuario planta no encontrado.', false);
            }

            if (
                WbFormulaCentroProduccion::where('fk_id_formula_lista', $req->formulaLista)
                ->where('fk_id_planta', $req->planta)
                ->get()->count() > 0
            ) {
                return $this->handleAlert('Esta formula de centro de produccion ya se encuentra registrada.', false);
            }
            $modeloRegistrar = new WbFormulaCentroProduccion;
            $modeloRegistrar->fk_id_formula_lista = $req->formulaLista;
            $modeloRegistrar->fk_id_planta = $req->planta;
            $modeloRegistrar->Estado = $req->estado;
            $modeloRegistrar->userCreator =  $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar->codigoFormulaCdp = $req->codigoFormulaCdp;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                //$modeloRegistrar = $this->registrar($req, $modeloRegistrar);

                if ($modeloRegistrar->save()) {
                    //$modeloRegistrar->id_formula_centroProduccion = $modeloRegistrar->latest('id_formula_centroProduccion')->first()->id_formula_centroProduccion;
                    //var_dump($modeloRegistrar);
                    return $this->handleResponse($req, $modeloRegistrar->latest('id_formula_centroProduccion')->first(), 'Formula de centro de produccion registrada.');
                }
            } catch (Exception $exc) {
                var_dump($exc);
            }

            return $this->handleAlert('Formula de centro de produccion no registrada.', false);
        }
    }

    public function registrar($centroProduccion)
    {
        try {
            if ($centroProduccion->save()) {
                return $centroProduccion;
            }
        } catch (Exception $exc) {
        }
        return null;
    }

    public function getPorFormulaLista(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleResponse($req, [], 'Consultado.');
        }
        $consulta = WbFormulaCentroProduccion::select(
            'id_Formula_centroProduccion',
            'fk_id_formula_lista',
            'fk_id_planta',
            'Wb_Formula_CentroProduccion.Estado',
            'dateCreate',
            'userCreator',
            'usuPlanta.NombrePlanta',
            'codigoFormulaCdp'
        )->leftjoin('usuPlanta', 'usuPlanta.id_plata', '=', 'Wb_Formula_CentroProduccion.fk_id_planta')
            ->where('Wb_Formula_CentroProduccion.Estado', 'A')
            ->where('Wb_Formula_CentroProduccion.fk_id_formula_lista', $id);
        $consulta = $this->filtrar($req, $consulta, 'Wb_Formula_CentroProduccion')->get();
        $ususPlanta = UsuPlanta::all();
        foreach ($consulta as $item) {
            $this->setUsuPlantaById($item, $ususPlanta);
        }
        return $this->handleResponse($req, $this->wbFormulaCentroProduccionToArray($consulta), __("messages.consultado"));
    }

    public function getPorFormulaListaYhito(Request $req, $formulaLista, $hito)
    {
        if (!is_numeric($formulaLista)) {
            return $this->handleResponse($req, [], 'Consultado.');
        }
        $consulta = WbFormulaCentroProduccion::select(
            'id_Formula_centroProduccion',
            'fk_id_formula_lista',
            'Wb_Formula_CentroProduccion.fk_id_planta',
            'Wb_Formula_CentroProduccion.Estado',
            'Wb_Formula_CentroProduccion.dateCreate',
            'Wb_Formula_CentroProduccion.userCreator',
            'codigoFormulaCdp',
            'usuPlanta.NombrePlanta',
            'fk_LocationID'
        )->leftjoin('usuPlanta', 'usuPlanta.id_plata', '=', 'Wb_Formula_CentroProduccion.fk_id_planta')
            ->leftJoin('Wb_CentroProduccion_Hitos as CP', 'CP.fk_id_planta', 'Wb_Formula_CentroProduccion.fk_id_planta')
            ->where('Wb_Formula_CentroProduccion.Estado', 'A')
            ->where('Wb_Formula_CentroProduccion.fk_id_formula_lista', $formulaLista)
            ->whereRaw("[id_formula_centroProduccion]  not in(
           SELECT TOP (1000) [id_formula_centroProduccion]
           FROM [Wb_Formula_CentroProduccion] as MP
           LEFT JOIN usuPlanta PL ON PL.id_plata = MP.fk_id_planta
           LEFT JOIN Wb_CentroProduccion_Hitos CP ON CP.fk_id_planta = MP.fk_id_planta
           WHERE [fk_id_formula_lista] = '" . $formulaLista . "'   AND fk_id_Hito = '" . $hito . "')")
            ->where('Wb_Formula_CentroProduccion.estado', 'A')
            ->where('usuPlanta.estado', 1)
            ->where('CP.Estado', 'A')
            ->groupBy(
                'id_formula_centroProduccion',
                'fk_id_formula_lista',
                'Wb_Formula_CentroProduccion.fk_id_planta',
                'Wb_Formula_CentroProduccion.Estado',
                'Wb_Formula_CentroProduccion.dateCreate',
                'Wb_Formula_CentroProduccion.userCreator',
                'codigoFormulaCdp',
                'usuPlanta.NombrePlanta',
                'usuPlanta.fk_LocationID'
            );
        $consulta = $this->filtrar($req, $consulta, 'Wb_Formula_CentroProduccion')->get();
        return $this->handleResponse($req, $this->wbFormulaCentroProduccionToArray($consulta), __("messages.consultado"));
    }

    public function inHabilitar(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert(__("messages.formula_centro_produccion_no_valido"));
        }
        $modelo = WbFormulaCentroProduccion::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.formula_centro_produccion_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.formula_centro_produccion_no_valido"));
        }
        $modelo->Estado = 'I';
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.formula_centro_produccion_inhablitado"));
            }
        } catch (Exception $exc) {
        }
        return $this->handleAlert(__("messages.formula_centro_produccion_no_inhablitado"));
    }

    public function habilitar(Request $request, $id)
    {
        if (!is_numeric($id)) {
            __("messages.formula_centro_produccion_no_valido");
        }
        $modelo = WbFormulaCentroProduccion::find($id);
        if ($modelo == null) {
            return $this->handleAlert(__("messages.formula_centro_produccion_no_encontrado"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.formula_centro_produccion_no_valido"));
        }
        $modelo->Estado = 'A';
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, [], __("messages.formula_centro_produccion_habilitado"));
            }
        } catch (Exception $exc) {
        }
        return $this->handleAlert(__("messages.formula_centro_produccion_no_habilitado"));
    }

    public function setUsuPlantaById($formulaCapa, $array)
    {
        for ($i = 0; $i < $array->count(); $i++) {
            if ($formulaCapa->fk_id_planta == $array[$i]->id_plata) {
                $reescribir = $this->usuPlantaToModel($array[$i]);
                $formulaCapa->objectUsuPlanta = $reescribir;
                break;
            }
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
        $consulta = WbFormulaCentroProduccion::select(
            'id_formula_centroProduccion',
            'fk_id_formula_lista',
            'fk_id_planta',
            'Wb_Formula_CentroProduccion.Estado',
            'dateCreate',
            'userCreator',
            'usuPlanta.NombrePlanta',
            'codigoFormulaCdp'
        )->leftjoin('usuPlanta', 'usuPlanta.id_plata', '=', 'Wb_Formula_CentroProduccion.fk_id_planta')
            ->where('Wb_Formula_CentroProduccion.Estado', 'A');
        if ($request->has('formula_lista')) {
            $consulta = $consulta->where('Wb_Formula_CentroProduccion.fk_id_formula_lista', $request->formula_lista);
        }
        $consulta = $this->filtrar($request, $consulta, 'Wb_Formula_CentroProduccion')->get();
        $ususPlanta = UsuPlanta::all();
        foreach ($consulta as $item) {
            $this->setUsuPlantaById($item, $ususPlanta);
        }
        return $this->handleResponse($request, $this->wbFormulaCentroProduccionToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

     public function getCentrobyFormulaHito(Request $request, $material, $hito){
        //se consultan los centros de produccion activos que cuentan con la disponiblidad del material
        $centros=WbFormulaCentroProduccion::with('centro')
            ->where('fk_id_formula_lista',$material)
            ->where('Estado','A');

        //se filtra por proyecto
        $centros = $this->filtrar($request, $centros, 'Wb_Formula_CentroProduccion')->get();

        //se valida si se debe limitar por hito o no
        if($hito!='all'){
            //consultamos los centros de produccion que estan autorizados a despachar al hito
            $CentrosPermitidos=WbCentroProduccionHitos::select('fk_id_planta')->where('fk_id_Hito',$hito)->where('Estado','A')->get();

            //ahora extraemos los centros permitidos para despachar
            $autorizados=$centros->whereIn('centro.id_plata',$CentrosPermitidos->pluck('fk_id_planta'));

            //ahora sacamos los no autorizados
            $noautorizado=$centros->whereNotIn('centro.id_plata',$CentrosPermitidos->pluck('fk_id_planta'))->where('centro.estado',1);

           //se crea la colleccion que va a rtener los dos datos.
            //se formatean los datos aprobados
            $respuesta=collect($this->WbPlantaAutorizadaToArray($autorizados->pluck('centro')->sortBy('NombrePlanta'),1));

            //se formatean los no aprobados
            $respuesta=$respuesta->merge($this->WbPlantaAutorizadaToArray($noautorizado->pluck('centro')->sortBy('NombrePlanta'),0));
        }else{
            //se envian todos los datos recibido
            $respuesta=collect($this->WbPlantaAutorizadaToArray($centros->pluck('centro')->sortBy('NombrePlanta'),1));
        }

        return $this->handleResponse($request,$respuesta->unique('identificador')->values(), __('messages.consultado'));


    }

    public function getFormulawithCenter(Request $request){
        //se consultan los formulas de produccion activos que cuentan con la disponiblidad del material
        $formulas=WbFormulaCentroProduccion::with('formula')
            ->where('Estado','A');

        //se filtra por proyecto
        $formulas = $this->filtrar($request, $formulas, 'Wb_Formula_CentroProduccion')->get();

        //se filtra por formulas activos y solicitables
        $formulas=$formulas->where('formula.Estado','A');

        //se envian todos los datos recibido
        $respuesta=collect($this->WbFormulaAutorizadoToArray($formulas->pluck('formula')->sortBy('Nombre'),1));

        return $this->handleResponse($request,$respuesta->unique('identificador')->values(), __('messages.consultado'));

    }
}
