<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\actividad;
use Illuminate\Http\Request;
use Exception;
class ActividadController extends BaseController implements Vervos
{
    public function get(Request $request) {
        $consulta = actividad::select('Actividades.*');
        $consulta = $this->filtrarPorProyecto($request, $consulta)->orderBy('Actividad', 'asc')->get();
        return $this->handleResponse($request, $this->actividadToArray($consulta), __("messages.consultado"));
    }

    public function getActividadBySolicitud(Request $request, $solicitud) {
        $consulta = actividad::select(
            'a.id_Actividad as identificador',
            'a.Actividad as nombre',
            'a.descripcion',
            'ar.fk_estado as estado',
            'ar.fk_solicituda as solicitud',
            'ar.fk_usuario as usuario',
            'ar.observacion as observacion'
        )->from('Actividades as a')
            ->leftJoin('liberaciones_actividades as ar', 'ar.fk_act', 'a.id_Actividad')
            ->where('fk_solicitud', $solicitud);
        $consulta = $this->filtrarPorProyecto($request, $consulta, 'a')->get();
        return $this->handleResponse($request, $consulta, __('messages.consultado'));
    }

    public function actividadesCompletasDeprecated(Request $request) {
        $consulta = actividad::select(
            'Actividades.id_Actividad as identificador',
            'Actividades.Actividad as nombre',
            'Actividades.descripcion',
            'ar.fk_estado as estado',
            'ar.fk_solicitud as solicitud',
            'ar.fk_usuario as usuario',
            'ar.observacion as observacionLiberacion'
        )->leftJoin('liberaciones_actividades as ar', 'ar.fk_act',  'Actividades.id_Actividad')->get();
        try {
            return $this->handleResponse($request, $consulta, 'Consultado');
        } catch (Exception $exc){ }
    }

    public function actividadesCompletas(Request $request) {
        $consulta = actividad::select(
            'Actividades.id_Actividad as identificador',
            'Actividades.Actividad as nombre',
            'Actividades.descripcion',
            'ar.fk_estado as estado',
            'ar.fk_solicitud as solicitud',
            'ar.fk_usuario as usuario',
            'ar.observacion as observacionLiberacion'
        )->leftJoin('liberaciones_actividades as ar', 'ar.fk_act',  'Actividades.id_Actividad');
        try {
            $consulta = $this->filtrarPorProyecto($request, $consulta, 'Actividades')->get();
            return $this->handleResponse($request, $consulta, 'Consultado');
        } catch (Exception $exc){ }
    }

    public function getEncript(Request $request, $proyecto) {

    }

    public function post(Request $req)
    {
        // TODO: Implement insert() method.
        if(!$req->json()->has('nombre')) {
            return $this->handleAlert('falta campo nombre.');
        }
        if(!$req->json()->has('descripcion')) {
            return $this->handleAlert('falta campo descripcion.');
        }
        $actividad = new actividad;
        $actividad->Actividad = $req->nombre;
        $actividad->descripcion = $req->descripcion;
        $actividad = $this->traitSetProyectoYCompania($req, $actividad);
        try {
            if ($actividad->save()) {
                return $this->handleResponse($req, [], 'Actividad registrada.');
            }
        } catch (Exception $exc) { }
        return $this->handleAlert('Actividad no registrada.');
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {

    }
}
