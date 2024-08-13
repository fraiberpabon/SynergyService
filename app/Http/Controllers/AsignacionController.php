<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\actividad;
use App\Models\Area;
use App\Models\Asignacion;
use Exception;
use Illuminate\Http\Request;

class AsignacionController extends BaseController implements Vervos
{
    public function post(Request $req) {
        if(!$req->json()->has('area')) {
            return $this->handleAlert('Falta campo area.');
        }
        if(!$req->json()->has('actividad')) {
            return $this->handleAlert('Falta campo actividad.');
        }
        if($req->validate([
            'area' => 'required|numeric',
            'actividad' => 'required|numeric'
        ])) {
            if(Area::find($req->area) == null) {
                return $this->handleAlert('Area no encontrada.');
            }
            if(actividad::find($req->actividad) == null) {
                return $this->handleAlert('Actividad no encontrada.');
            }
            if(Asignacion::where('fk_area', $req->area)->where('fk_actividad', $req->actividad)->count() > 0){
                return $this->handleAlert('Ya existe esta asignacion.');
            }
            $modeloRegistrar = new Asignacion;
            $modeloRegistrar->fk_area = $req->area;
            $modeloRegistrar->fk_actividad = $req->actividad;
            $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);
            try {
                if($modeloRegistrar->save()) {
                    return $this->handleResponse($req, [], 'Asignacion registrada.');
                }
            } catch(Exception $exc) {}
            return $this->handleAlert('Asignacion no registrada.');
        }
    }

    public function getByArea(Request $req, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Area no valida.');
        }
        $consulta = Asignacion::select(
            'Asignacion.fk_actividad',
            'Asignacion.fk_area',
            'Actividades.Actividad',
            'Area.Area',
        )
        ->leftjoin('Area', 'Asignacion.fk_area', '=', 'Area.id_area')
        ->leftjoin('Actividades', 'Actividades.id_Actividad', '=', 'Asignacion.fk_actividad')
        ->where('fk_area',$id);
        $consulta = $this->filtrar($req, $consulta, 'Asignacion')->get();
        return $this->handleResponse($req, $consulta, __("messages.consultado"));
    }

    public function get(Request $request) {
        $areas = Area::all();
        $actividades = actividad::all();
        $consulta = Asignacion::select('Asignacion.*')
            ->leftJoin('Actividades', 'Actividades.id_Actividad', 'Asignacion.fk_actividad')
            ->orderBy('Actividades.Actividad');
        $consulta = $this->filtrar($request, $consulta, 'Asignacion')->get();
        foreach ($consulta as $item) {
            $this->setArea($item, $areas);
            $this->setActividad($item, $actividades);
        }
        return $this->handleResponse($request, $this->asignacionToArray($consulta), __("messages.consultado"));
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    private function setArea($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_area == $array[$i]->id_area) {
                $reescribir["identificador"] = $array[$i]->id_area;
                $reescribir["nombre"] = $array[$i]->Area;
                $estructura->objectArea = $reescribir;
                break;
            }
        }
    }

    private function setActividad($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_actividad == $array[$i]->id_Actividad) {
                $reescribir["identificador"] = $array[$i]->id_Actividad;
                $reescribir["nombre"] = $array[$i]->Actividad;
                $reescribir["descripcion"] = $array[$i]->descripcion;
                $estructura->objectActividad = $reescribir;
                break;
            }
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        $areas = WbConfiguraciones::all();
        $actividades = actividad::all();
        $consulta = Asignacion::select('Asignacion.*');
        $consulta = $this->filtrar($request, $consulta)->get();
        foreach ($consulta as $item) {
            $this->setArea($item, $areas);
            $this->setActividad($item, $actividades);
        }
        return $this->handleResponse($request, $this->asignacionToArray($consulta), __("messages.consultado"));
    }
}
