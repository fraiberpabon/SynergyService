<?php

namespace App\Http\Controllers\ParteDiario;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Http\interfaces\Vervos;
use App\Models\ParteDiario\Interrupciones;
use App\Models\ParteDiario\WbInterrupciones;
use Illuminate\Support\Facades\Validator;
use Exception;
class InterrupcionesController extends BaseController implements Vervos
{
    public function post(Request $req)
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
            $proyecto = $this->traitGetProyectoCabecera($request);
            $query = WbInterrupciones::where('estado', 1)
                ->where('fk_id_project_Company', $proyecto);
            $result =$query->get();

            return $this->handleResponse(
                $request,
                $this->WbInterrupcionesToArray($result),
                __('messages.consultado')
            );
        } catch (Exception $e) {
             \Log::error('error get conductores ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_interno_del_servidor'), false);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
    
}
