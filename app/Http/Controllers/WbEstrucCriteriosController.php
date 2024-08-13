<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbEstrucConfig;
use App\Models\WbEstrucCriterios;
use Exception;
use Illuminate\Http\Request;

class WbEstrucCriteriosController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('estrucConfig')) {
            return $this->handleAlert('Falta campo estrucConfig.');
        }
        if(!$req->json()->has('nombreCriterio')) {
            return $this->handleAlert('Falta campo nombreCriterio.');
        }
        if(!$req->json()->has('criterio1')) {
            return $this->handleAlert('Falta campo criterio1.');
        }
        if(!$req->json()->has('operacion')) {
            return $this->handleAlert('Falta campo operacion.');
        }
        if(!$req->json()->has('criterio2')) {
            return $this->handleAlert('Falta campo criterio2.');
        }
        if($req->validate([
            'estrucConfig'=> 'numeric',
            'nombreCriterio'=> 'nullable|string',
            'criterio1'=> 'string',
            'operacion'=> 'string',
            'criterio2'=> 'string'
        ])) {
            if(WbEstrucConfig::find($req->estrucConfig) == null) {
                return $this->handleAlert('Estruc config no encontrado.');
            }
            if(WbEstrucCriterios::where('fk_estruc_config', $req->estrucConfig)
            ->where('nombre_criterio')
            ->get()->count() > 0) {
                return $this->handleAlert('Ya existe un criterio con el mismo nombre y configuracion.');
            }
            $modeloRegistrar = new WbEstrucCriterios;
            $modeloRegistrar->fk_estruc_config = $req->estrucConfig;
            $modeloRegistrar->nombre_criterio = $req->nombreCriterio;
            $modeloRegistrar->criterio1 = $req->criterio1;
            $modeloRegistrar->operacion = $req->operacion;
            $modeloRegistrar->criterio2 = $req->criterio2;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    $modeloRegistrar->id_estruc_criterio = $modeloRegistrar->latest('id_estruc_criterio')->first()->id_estruc_criterio;
                    return $this->handleResponse($req, $modeloRegistrar, 'Estruc criterio registrada.');
                }
            } catch(Exception $exc){}
            return $this->handleAlert('Estruc criterio no registrada.');
        }
    }

    public function update(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Estruc config no valido.');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if($req->validate([
            'estado' => 'numeric|max:2',
        ])) {
            $modeloModificar = WbEstrucCriterios::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert('Estruc criterio no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Estruc criterio no valido.');
            }
            $modeloModificar->estado = $req->estado;
            try {
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Estruc criterio modificado.');
                }
            } catch(Exception $exc){}
            return $this->handleAlert('Estruc criterio no modificado.');
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
        $consulta = WbEstrucCriterios::select();
        if ($request->has('estructConfig')) {
            $consulta = $consulta->where('fk_estruc_config', $request->estructConfig);
        }
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->wbEstructCriterioToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
