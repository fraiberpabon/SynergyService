<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\estruc_tipos;
use App\Models\WbEstrucConfig;
use App\Models\WbEstrucConfigAsign;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbEstrucConfigAsignController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('estrucConfig')) {
            return $this->handleAlert('Falta campo estrucConfig.', false);
        }
        if(!$req->json()->has('estrucTipo')) {
            return $this->handleAlert('Falta campo estrucTipo.', false);
        }
        if($req->validate([
            'estrucConfig' => 'numeric',
            'estrucTipo' => 'numeric'
        ])) {
            if(WbEstrucConfig::find($req->estrucConfig) == null) {
                return $this->handleAlert('Estruc config no encontrado.', false);
            }
            if(estruc_tipos::find($req->estrucTipo) == null) {
                return $this->handleAlert('Estruc tipo no encontrado.', false);
            }
            if(WbEstrucConfigAsign::where('fk_estruc_config', $req->estrucConfig)
            ->where('fk_estruc_tipo', $req->estrucTipo)->get()->count() > 0) {
                return $this->handleAlert('este estruc criterio asign ya se encuentra registrado', false);
            }
            $modeloRegistrar = new WbEstrucConfigAsign;
            $modeloRegistrar->fk_estruc_config = $req->estrucConfig;
            $modeloRegistrar->fk_estruc_tipo = $req->estrucTipo;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try{
                if($modeloRegistrar->save()) {
                    $modeloRegistrar->id_Estruc_Config_Asign = $modeloRegistrar->latest('id_Estruc_Config_Asign')->first()->id_Estruc_Config_Asign;
                    return $this->handleResponse($req, [], 'Estruc criterio asign registrada.');
                }
            } catch(Exception $exc) { }
            return $this->handleAlert('Estruc config asign no registrada.', false);
        }
    }

    public function delete(Request $request, $id){
        if(!is_numeric($id)) {
            return $this->handleAlert('Estruc config asign no valido.');
        }
        $modeloEliminar = WbEstrucConfigAsign::find($id);
        if ($modeloEliminar == null) {
            return $this->handleAlert('Estruc config asign no encontrado.');
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modeloEliminar->fk_id_project_Company != $proyecto) {
            return $this->handleAlert('Estruc config asign no valido.');
        }
        DB::table('Wb_Estruc_Config_Asign')
            ->where('id_Estruc_Config_Asign', $id)
            ->delete();
        return $this->handleResponse($request, [], 'Eliminado.');
    }

    public function getById(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Estruc config asign no valido.', false);
        }
        $consulta = WbEstrucConfigAsign::where('id_Estruc_Config_Asign', $id);
        $consulta = $this->filtrar($req, $consulta)->first();
        return $this->handleResponse($req, $consulta, __("messages.consultado"));
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)
    {
        $consulta = WbEstrucConfigAsign::select();
        $consulta = $this->filtrar($request, $consulta)->get();
        $estructsTipo = estruc_tipos::all();
        $estructsConfig = WbEstrucConfig::all();
        foreach ($consulta as $item) {
            $this->setEstructConfigById($item, $estructsConfig);
            $this->setEstructTipoById($item, $estructsTipo);
        }
        return $this->handleResponse($request, $this->wbEstructConfigAsignToArray($consulta), __("messages.consultado"));
    }

    public function setEstructTipoById($formulaCapa, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($formulaCapa->fk_estruc_tipo == $array[$i]->id) {
                $reescribir = $this->estructTipostoModel($array[$i]);
                $formulaCapa->objectEstructTipo = $reescribir;
                break;
            }
        }
    }

    public function setEstructConfigById($formulaCapa, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($formulaCapa->fk_estruc_config == $array[$i]->id_estruc_config) {
                $reescribir = $this->wbEstructConfigToModel($array[$i]);
                $formulaCapa->objectEstructConfig = $reescribir;
                break;
            }
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
