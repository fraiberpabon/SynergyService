<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\TipoMezcla;
use Illuminate\Http\Request;

class TipoMezclaController extends BaseController implements Vervos
{
    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        if (!$req->has('tipo')) {
            return $this->handleAlert(__("messages.falta_campo_nombre"));
        }
        $modelo = new TipoMezcla;
        $modelo->Tipo = $req->tipo;
        $modelo->Estado = 1;
        $modelo = $this->traitSetProyectoYCompania($req, $modelo);
        try {
            if ($modelo->save()) {
                return $this->handleResponse($req, [], __("messages.mezcla_registrada"));
            }
        } catch (\Exception $exc){ }
        return $this->handleAlert(__("messages.mezcla_no_registrada"));
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
        $consulta = TipoMezcla::select();
        $consulta = $this->filtrarPorProyecto($request, $consulta)->get();
        return $this->handleResponse($request, $this->tipoMezclaToArray($consulta), __("messages.consultado"));
    }

    public function getActivos(Request $request)
    {
        $consulta = TipoMezcla::select(
            'TipoMezcla.Id as identificador',
            'TipoMezcla.Tipo as tipo',
            'F.id as idFormula',
            'F.formula',
            'F.resistencia',
            'F.dmx',
        )->leftJoin('Formula as F', 'F.fk_tipoMezcla', 'TipoMezcla.Id')
        ->where('F.estado', 1);
        $consulta = $this->filtrarPorProyecto($request, $consulta, 'TipoMezcla')->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    public function getParaSync(Request $request)
    {
        $consulta = TipoMezcla::select(
            'TipoMezcla.Tipo',
            'F.formula',
            'F.resistencia',
            'F.dmx',
            'TipoMezcla.Id',
            'F.id'
        )->leftJoin('Formula as F', 'F.fk_tipoMezcla', 'TipoMezcla.Id');
        $consulta = $this->filtrarPorProyecto($request, $consulta, 'TipoMezcla')->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }


}
