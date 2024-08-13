<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Area;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AreaController extends BaseController implements  Vervos
{
    /**
     * Inserta un registro de area a la base de datos
     * @param Request $req
     * @return JsonResponse|void
     */
    public function post(Request $req) {
        if(!$req->json()->has('nombre')) {
            return $this->handleAlert(__("messages.falta_campo_nombre"));
        }
        if($req->validate([
            'nombre' => 'string|max:50',
            'proyecto' => 'required|numeric|max:10',
        ])) {
            if(Area::where('Area', $req->nombre)->where('fk_id_project_Company', $req->proyecto)->count() > 0) {
                return $this->handleAlert( __("messages.ya_hay_otra_area_con_el_mismo_nombre"));
            }
            $modelo = new Area;
            $modelo->Area = $req->nombre;
            $modelo->estado = 1;
            $modelo = $this->traitSetProyectoYCompania($req, $modelo);
            try {
                if($modelo->save()) {
                    $modelo->id_area = $modelo->latest('id_area')->first()->id_area;
                    return $this->handleResponse($req, $this->areaToModel($modelo), __("messages.area_registrada"));
                }
            } catch(Exception $exc) { }
            return $this->handleAlert(__("messages.area_no_registrada"));
        }
    }

    /**
     * Elimina un area por id
     * @param $id
     * @return JsonResponse
     */
    public function delete(Request $request, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert(__("messages.area_no_valida"));
        }
        $areaEliminar = Area::find($id);
        if($areaEliminar == null) {
            return $this->handleAlert(__("messages.area_no_encontrada"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($areaEliminar->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.area_no_valida"));
        }
        try {
            DB::table('Area')
            ->where('id_area', $id)
            ->delete();
            return $this->handleResponse($request, [], __("messages.area_no_valida"));
        } catch(Exception $exc) { }
        return $this->handleAlert( __("messages.area_no_valida"),);
    }

    public function bloquear(Request $request, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert(__("messages.area_no_valida"));
        }
        $modelo = Area::find($id);
        if($modelo == null) {
            return $this->handleAlert(__("messages.area_no_encontrada"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.area_no_valida"));
        }
        $modelo->estado = 0;
        try {
            if ($modelo->save()) {
                return $this->handleResponse($request, $modelo, __("messages.area_bloqueada"));
            }
        } catch(Exception $exc) { }
        return $this->handleAlert(__("messages.area_no_bloqueada"));
    }

    public function desbloquear(Request $request, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert(__("messages.area_no_valida"));
        }
        $areaEliminar = Area::find($id);
        if($areaEliminar == null) {
            return $this->handleAlert(__("messages.area_no_encontrada"));
        }
        $proyecto = $this->traitGetProyectoCabecera($request);
        if ($areaEliminar->fk_id_project_Company != $proyecto) {
            return $this->handleAlert(__("messages.area_no_valida"));
        }
        $areaEliminar->estado = 1;
        try {
            if ($areaEliminar->save()) {
                return $this->handleResponse($request, [], __("messages.area_desbloqueada"));
            }
        } catch(Exception $exc) { }
        return $this->handleAlert(__("messages.area_no_desbloqueada"));
    }

    /**
     * Consulta de todas las areas
     * @return JsonResponse
     */
    public function get(Request $request) {
        $consulta = Area::select('area.*');
        if($request->estado) {
            $consulta = $consulta->where('estado', $request->estado);
        }
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->areaToArray($consulta), __("messages.consultado"));
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        $consulta = Area::select('area.*')->where('fk_id_project_Company', $proyecto);
        if($request->estado) {
            $consulta = $consulta->where('estado', $request->estado);
        }
        $consulta = $consulta->get();
        return $this->handleResponse($request, $this->areaToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyectoParaRegistro(Request $request, $proyecto)
    {$consulta = Area::where('estado', $request->estado)
        ->where('fk_id_project_Company', $proyecto)->get();
        return $this->handleResponse($request, $this->areaToArray($consulta), __("messages.consultado"));

    }
}
