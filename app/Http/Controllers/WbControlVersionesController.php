<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\wbControlVersiones;
use Illuminate\Http\Request;

class WbControlVersionesController extends BaseController implements Vervos
{

    /**
     * @param Request $req
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param Request $request
     */
    public function get(Request $request)
    {

    }

    public function getByVersion(Request $request){
        if ($request->app == null || strlen($request->app) == 0) {
            return $this->handleAlert(__('messages.version_no_valida'));
        }
        $consulta = wbControlVersiones::where('Aplicacion', $request->app)->where('Estado', 'A')->first();
        if ($consulta) {
            return $this->handleResponse($request, $consulta, __('messages.consultado'));
        } else {
            return $this->handleAlert(__('messages.consultado'));
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
