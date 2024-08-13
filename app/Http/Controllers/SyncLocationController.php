<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncLocationController extends BaseController implements  Vervos
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

    public function byEstado(Request $request, $estado) {
        if (!($estado == 'A' || $estado == 'I' || $estado == 'todo')) {
            return $this->handleAlert('Faltan parametros para la consulta.');
        }
        $consulta = location::select(
            'loc.ID',
            'loc.LocationDesc',
            'loc.LocationID',
        )
            ->leftJoin('TimeScanSI.dbo.JobLocation as job', 'job.LocationID',  'Location.LocationID');
        if ($estado !== 'todo') {
            $consulta = $consulta->where('Status', $estado);
        }
        $consulta = $consulta->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    public function byEstadoJob(Request $request, $estado, $frente) {
        if (!($estado == 'A' || $estado == 'I' || $estado == 'todo')) {
            return $this->handleAlert('Faltan parametros para la consulta.');
        }
        $consulta = location::select(
            'Location.ID',
            'Location.LocationDesc',
            'Location.LocationID',
        )
            ->leftJoin('TimeScanSI.dbo.JobLocation as job', 'job.LocationID',  'Location.LocationID')
        ->where('job.JobID', $frente);
        if ($estado !== 'todo') {
            $consulta = $consulta->where('Status', $estado);
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
