<?php

namespace App\Http\Controllers\MotivosInterrupcion;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Http\interfaces\Vervos;
use App\Models\MotivosInterrupcion\MotivosInterrupcion;
use Exception;
use Carbon\Carbon;
use PhpParser\Node\Stmt\Else_;
use Illuminate\Support\Facades\Log;
class MotivosInterrupcionController extends BaseController implements Vervos
{

    public function getMobile(Request $request)
    {
        try {
            $proyecto = $this->traitGetProyectoCabecera($request);
            $query = MotivosInterrupcion::where('estado', 1)
                ->where('fk_id_project_Company', $proyecto);
            $result = $query->get();

            return $this->handleResponse(
                $request,
                $this->WbMotivoInterrupcionToArray($result),
                __('messages.consultado')
            );
        } catch (Exception $e) {
            \Log::error('error motivo interrupcion ' . $e->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
        }
    }







    /**
     * @param Request $req
     */
    public function post(Request $req)
    {
    }

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id)
    {
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id)
    {
    }

    /**
     * @param Request $request
     */
    public function get(Request $request)
    {
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
    }


}
