<?php

namespace App\Http\Controllers\Turnos;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Http\interfaces\Vervos;
use Illuminate\Support\Facades\Validator;
use App\Models\Turnos\SyTurnos;
use Exception;
class SyTurnosController extends BaseController implements Vervos
{
   /**
     * @param Request $req
     */
    public function post(Request $req){}

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id){}

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id){}

    /**
     * @param Request $request
     */
    public function get(Request $request){}

    public function getPorProyecto(Request $request, $proyecto){}

    public function getTurnos(Request $request){
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $turnos = SyTurnos::where('fk_id_project_Company', $proyecto)->where('estado', 1)->get();

            if (count($turnos) == 0) {
                return $this->handleAlert(__('messages.no_tiene_turnos_registrados'), false);
            }
            return $this->handleResponse($request, $this->SyTurnosEquiposArray($turnos), __('messages.consultado'));
        } catch (Exception $e) {
            \Log::error('error al obtener parte diario ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }
}
