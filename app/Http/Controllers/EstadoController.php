<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\estado;
use Illuminate\Http\Request;

class EstadoController extends BaseController implements Vervos
{
    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function get(Request $request)
    {
        $consulta = estado::where('para_estructura', 0)->whereNotNull('descripcion_estado')->orderby('descripcion_estado')->get();
        return $this->handleResponse($request, $this->estadoToArray($consulta), __("messages.consultado"));
    }

    public function getParaEstructura(Request $request) {
        $consulta = estado::where('para_estructura', 1)->orderby('descripcion_estado')->get();
        return $this->handleResponse($request, $this->estadoToArray($consulta), __("messages.consultado"));
    }

    public function activos(Request $request) {
        $consulta = estado::where('para_estructura', 1)->orderby('descripcion_estado')->get();
        return $this->handleResponse($request, $this->estadoToArray($consulta), __("messages.consultado"));
    }


    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
