<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbTipoDeObra;
use Illuminate\Http\Request;

class WbTipoDeObraController extends BaseController implements Vervos
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
        $consulta = WbTipoDeObra::select('*');
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->wbTipoDeObraToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
