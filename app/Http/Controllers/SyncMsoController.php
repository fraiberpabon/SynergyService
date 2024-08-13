<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\SyncMso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncMsoController extends BaseController implements  Vervos
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

    public function msoParaViajeBascula(Request $request) {
        $baseDatos = Db::connection('sqlsrv2')->getDatabaseName().'.dbo.';
        $consulta = SyncMso::select(
            'TimeScanSI.dbo.MSO.MSOID as ID',
            'TimeScanSI.dbo.MSO.MSODesc as NOMBRE',
        )
            ->selectRaw("
                (
						select  sum(convert(int,peso3))
						from {$baseDatos}sync_registros
						where convert(datetime,   REPLACE(fecha,'-','')) >= convert(datetime,GETDATE()-1)
						and producto = TimeScanSI.dbo.MSO.MSOID
						collate SQL_Latin1_General_CP1_CI_AS
					) as PESO
            ")
            ->orderBy('PESO')
        ->get();
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
