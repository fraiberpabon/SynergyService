<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\SyncJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncJobsController extends BaseController implements  Vervos
{
    /**
     * Inserta un registro de area a la base de datos
     * @param Request $req
     * @return JsonResponse|void
     */
    public function post(Request $req) {

    }

    /**
     * Elimina un area por id
     * @param $id
     * @return JsonResponse
     */
    public function delete(Request $request, $id) {

    }

    /**
     * Consulta de todas las areas
     * @return JsonResponse
     */
    public function get(Request $request) {

    }

    public function jobsParaViajeBascula(Request $request, $estado) {
        if (!$estado == 'A' || $estado == 'I' || $estado == 'todo') {
            return $this->handleAlert('Faltan parametros para la consulta.');
        }
        $consulta = SyncJobs::select(
            'JOBID',
            'JOBNAME',
        );
        if ($estado !== 'a') {
            $consulta = $consulta->where('JOBStatus', $estado);
        }
        $consulta = $consulta->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {

    }

    public function getPorProyectoParaRegistro(Request $request, $proyecto)
    {

    }
}
