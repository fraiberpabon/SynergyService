<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbLiberacionesFormato;
use Illuminate\Http\Request;

class WbLiberacionesFormatoController extends BaseController implements Vervos
{

    private $formatosSyngergy = ['1010', '1009', '1008'];
    public function post(Request $req)
    {
    }

    public function postArray(Request $req)
    {
    }

    /**
     * Funcion de update no tocar por la interface de vervos
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * Funcion de delete no tocar por la interface de vervos
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * Funcion de get no tocar por la interface de vervos
     */
    public function get(Request $request)
    {
        try {
            $query = WbLiberacionesFormato::where('estado', 1)->whereIn('fk_tipo_formato', $this->formatosSyngergy);
            $query = $this->filtrarPorProyecto($request, $query)->get();

            return $this->handleResponse($request, $this->WbLibFormatoToArray($query), __('messages.consultado'));
        } catch (\Exception $e) {
            Log::error('error get liberaciones formato ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
