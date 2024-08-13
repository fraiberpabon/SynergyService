<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\usuarios_M;
use App\Models\WbHitos;
use App\Models\WbTramos;
use App\Models\WbTramosHitosAsign;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbTramosHitosAsignController extends BaseController implements Vervos
{
    public function getByTramo(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Tramo no valido.', false);
        }
        $consulta = WbTramosHitosAsign::select(
            'Id_tramos_hitos as Id',
            'fk_id_Tramo as fk_Tramo',
            'fk_id_Hitos as fk_Hitos',
            'Wb_Tramos_Hitos_Asign.Estado',
            DB::raw("CONVERT(varchar,[dateCreate],22)  as dateCreate"),
            'Descripcion'
        )->leftjoin('Wb_Hitos', 'Wb_Hitos.Id_Hitos', '=', 'Wb_Tramos_Hitos_Asign.fk_id_Hitos')
        ->where('fk_id_Tramo', $id)
        ->where('Wb_Tramos_Hitos_Asign.Estado', 'A');
        $consulta = $this->filtrar($req, $consulta, 'Wb_Tramos_Hitos_Asign')->get();
        return $this->handleResponse($req, $consulta, __("messages.consultado"));
    }

    public function updatePorTramoYHito(Request $req, $tramo, $hito) {
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if(!$req->json()->has('userCreator')) {
            return $this->handleAlert('Falta campo userCreator.');
        }
        if($req->validate([
            'estado' => 'string',
            'userCreator' => 'numeric'
        ])) {
            if(WbTramos::where('Id_Tramo', $tramo)->get()->count() == 0) {
                return $this->handleAlert('Tramo no encontrado');
            }
            if(WbHitos::where('Id_Hitos', $hito)->first() == null) {
                return $this->handleAlert('Hito no encontrado.');
            }
            if(usuarios_M::find($req->userCreator) == null) {
                return $this->handleAlert('Usuario no encontrado.');
            }
            $modeloModificar = WbTramosHitosAsign::where('fk_id_Tramo', $tramo)
            ->where('fk_id_Hitos', $hito)
            ->where('Estado', 'A')
            ->first();
            if($modeloModificar == null) {
                return $this->handleAlert('Tramo hito asign no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Tramo hito asign no valido.');
            }
            $modeloModificar->Estado = $req->estado;
            $modeloModificar->userCreator = $req->userCreator;
            try {
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Tramo hito asign modificado.');
                }
            } catch(Exception $exc){}
            return $this->handleAlert('Tramo hito asign no modificado.');
        }
    }

    public function update(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Tramo hito asign no valido.');
        }
        if(!$req->json()->has('tramo')) {
            return $this->handleAlert('Falta campo tramo.');
        }
        if(!$req->json()->has('hito')) {
            return $this->handleAlert('Falta campo hitos.');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if($req->validate([
            'tramo' => 'string',
            'hito' => 'string',
            'estado' => 'string'
        ])) {
            if(WbTramos::where('Id_Tramo', $req->tramo)->get()->count() == 0) {
                return $this->handleAlert('Tramo no encontrado');
            }
            if(WbHitos::where('Id_Hitos', $req->hito)->first() == null) {
                return $this->handleAlert('Hito no encontrado.');
            }
            $modeloModificar = WbTramosHitosAsign::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert('Tramo hito asign no encontrado.');
            }
            $proyecto = $this->traitGetProyectoCabecera($req);
            if ($modeloModificar->fk_id_project_Company != $proyecto) {
                return $this->handleAlert('Tramo hito asign no valido.');
            }
            $modeloModificar->fk_id_Tramo = $req->tramo;
            $modeloModificar->fk_id_Hitos = $req->hito;
            $modeloModificar->Estado = $req->estado;
            $modeloModificar->userCreator = $this->traitGetIdUsuarioToken($req);
            try {
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Tramo hito asign modificado.');
                }
            } catch(Exception $exc){}
            return $this->handleAlert('Tramo hito asign no modificado.');
        }
    }

    public function asignacionMasiva(Request $req) {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $asignacionesFallidas = array();
        $eliminacionesFallidas = array();
        if (!is_array($req->dataAsignar) || !is_array($req->dataEliminar)){
            return $this->handleAlert('Datos invalidos1.');
        }
        foreach ($req->dataAsignar as $item) {
            if(WbTramos::where('Id_Tramo', $item['tramo'])->first() == null) {
                array_push($asignacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos2.');
            }
            if(WbHitos::where('Id_Hitos', $item['hito'])->first() == null) {
                array_push($asignacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos3.');
            }
            if (WbTramosHitosAsign::where('fk_id_Tramo', $item['tramo'])->where('fk_id_Hitos', $item['hito'])->where('estado', 'A')->first() != null) {
                array_push($asignacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos4.');
            }
        }
        foreach ($req->dataEliminar as $item) {
            if(WbTramos::where('Id_Tramo', $item['tramo'])->get()->count() == 0) {
                array_push($eliminacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos5.');
            }
            if(WbHitos::where('Id_Hitos', $item['hito'])->first() == null) {
                array_push($eliminacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos6.');
            }
            $modelo = WbTramosHitosAsign::find($item['identificador']);
            if($modelo == null) {
                array_push($eliminacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos7.');
            }
            if ($modelo->fk_id_project_Company != $proyecto) {
                array_push($eliminacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos8.');
            }
        }
        foreach ($req->dataAsignar as $item) {
            if(WbTramos::where('Id_Tramo', $item['tramo'])->first() == null) {
                array_push($asignacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos9.');
            }
            if(WbHitos::where('Id_Hitos', $item['hito'])->first() == null) {
                array_push($asignacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos10.');
            }
            if (WbTramosHitosAsign::where('fk_id_Tramo', $item['tramo'])->where('fk_id_Hitos', $item['hito'])->where('estado', 'A')->first() != null) {
                array_push($asignacionesFallidas, $item);
                return $this->handleAlert('Datos invalidos11.');
            }
            $modelo = new WbTramosHitosAsign;
            $modelo->fk_id_Tramo = $item['tramo'];
            $modelo->fk_id_Hitos = $item['hito'];
            $modelo->Estado = $item['estado'];
            $modelo->userCreator = $this->traitGetIdUsuarioToken($req);
            $modelo = $this->traitSetProyectoYCompania($req, $modelo);
            try {
                $modelo->save();
            } catch(Exception $exc){
                array_push($asignacionesFallidas, $item);
            }
        }
        foreach ($req->dataEliminar as $item) {
            if(WbTramos::where('Id_Tramo', $item['tramo'])->first() == null) {
                array_push($eliminacionesFallidas, $item);
                break;
            }
            if(WbHitos::where('Id_Hitos', $item['hito'])->first() == null) {
                array_push($eliminacionesFallidas, $item);
                break;
            }
            $modelo = WbTramosHitosAsign::find($item['identificador']);
            if($modelo == null) {
                array_push($eliminacionesFallidas, $item);
                break;
            }
            if ($modelo->fk_id_project_Company != $proyecto) {
                array_push($eliminacionesFallidas, $item);
                break;
            }
            $modelo->Estado = 'I';
            $modelo->userCreator = $this->traitGetIdUsuarioToken($req);
            try {
                $modelo->save();
            } catch(Exception $exc){
                array_push($eliminacionesFallidas, $item);
            }
        }

        return $this->handleResponse($req, [
            'eliminacionesFallidad' => $eliminacionesFallidas,
            'asignacionesFallidas' => $asignacionesFallidas
        ], 'Asignacion masiva completada.');
    }

    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        if(!$req->json()->has('tramo')) {
            return $this->handleAlert('Falta campo tramo.');
        }
        if(!$req->json()->has('hito')) {
            return $this->handleAlert('Falta campo hitos.');
        }
        if(!$req->json()->has('estado')) {
            return $this->handleAlert('Falta campo estado.');
        }
        if($req->validate([
            'tramo' => 'string',
            'hito' => 'string',
            'estado' => 'string'
        ])) {
            if(WbTramos::where('Id_Tramo', $req->tramo)->get()->count() == 0) {
                return $this->handleAlert('Tramo no encontrado');
            }
            if(WbHitos::where('Id_Hitos', $req->hito)->first() == null) {
                return $this->handleAlert('Hito no encontrado.');
            }
            $modeloRegistrar = new WbTramosHitosAsign;
            $modeloRegistrar->fk_id_Tramo = $req->tramo;
            $modeloRegistrar->fk_id_Hitos = $req->hito;
            $modeloRegistrar->Estado = $req->estado;
            $modeloRegistrar->userCreator = $this->traitGetIdUsuarioToken($req);
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    return $this->handleResponse($req, [], 'Tramo hito asign registrado.');
                }
            } catch(Exception $exc){}
            return $this->handleAlert('Tramo hito asign no registrado.');
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
        $consulta = WbTramosHitosAsign::select()
            ->leftjoin('Wb_Hitos', 'Wb_Hitos.Id_Hitos', '=', 'Wb_Tramos_Hitos_Asign.fk_id_Hitos');
        if ($request->tramo) {
            $consulta = $consulta->where('fk_id_Tramo', $request->tramo);
        }
        if ($request->estado) {
            $consulta = $consulta->where('Wb_Tramos_Hitos_Asign.Estado', $request->estado);
        }
        $consulta = $this->filtrar($request, $consulta, 'Wb_Tramos_Hitos_Asign')->get();
        $hitos = WbHitos::all();
        foreach ($consulta as $item) {
            $this->setHitoById($item, $hitos);
        }
        return $this->handleResponse($request, $this->tramoHitoAsignToArray($consulta), __("messages.consultado"));
    }

    public function setHitoById($model, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($model->fk_id_Hitos == $array[$i]->Id_Hitos) {
                $reescribir = $this->wbHitosToModel($array[$i]);
                $model->objectHito = $reescribir;
                break;
            }
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function getActivosDeprecated(Request $request, $proyectid){
        $tramos_hitos=WbTramosHitosAsign::
        join("Wb_Tramos","Wb_Tramos.Id_Tramo","=","Wb_Tramos_Hitos_Asign.fk_id_Tramo")
            ->join("Wb_Hitos","Wb_Hitos.Id_Hitos","=","Wb_Tramos_Hitos_Asign.fk_id_Hitos")
            ->where("Wb_Tramos.Estado","!=","I")
            ->where("Wb_Hitos.Estado","!=","I")
            ->where("Wb_Tramos.fk_id_project_Company","=",$proyectid)
            ->where("Wb_Hitos.fk_id_project_Company","=",$proyectid)
            ->get();
        return $this->handleResponse($request, $this->toArray($tramos_hitos), 'success');
    }

    public function getActivos(Request $request){
        $tramos_hitos=WbTramosHitosAsign::
                join("Wb_Tramos","Wb_Tramos.Id_Tramo","=","Wb_Tramos_Hitos_Asign.fk_id_Tramo")
                ->join("Wb_Hitos","Wb_Hitos.Id_Hitos","=","Wb_Tramos_Hitos_Asign.fk_id_Hitos")
                ->where("Wb_Tramos.Estado","!=","I")
                ->where("Wb_Hitos.Estado","!=","I");
        $tramos_hitos = $this->filtrarPorProyecto($request, $tramos_hitos, 'Wb_Tramos_Hitos_Asign')->get();
        return $this->handleResponse($request, $this->toArray($tramos_hitos), 'success');
    }

    function toModel($modelo): array{
        return
            [
               'ID'=>$modelo['Id_tramos_hitos'],
               'TRAMO'=>$modelo['fk_id_Tramo'],
               'HITO'=>$modelo['fk_id_Hitos']

            ];
    }
}
