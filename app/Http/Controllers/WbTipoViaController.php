<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbTipoVia;
use Illuminate\Http\Request;

class WbTipoViaController extends BaseController implements Vervos
{
    /**
     * @param Request $req
     * @return void
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
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
        // TODO: Implement get() method.
        $consulta = WbTipoVia::select();
        if ($request->has('estado')) {
            $consulta = $consulta->wheere('estado', $request->estado);
        }
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->wbTipoViaToArray($consulta), __("messages.consultado"));
    }

    public function getActivos(Request $request)
    {
        $consulta = WbTipoVia::where('Estado', 'A');
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->wbTipoViaToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function find($id)
    {
        return WbTipoVia::find($id);
    }

    public function findActive($id)
    {
        return WbTipoVia::where('id_tipo_via', $id)->where('Estado', 'A')->first();
    }
}
