<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\SyncItemsTransportPaines;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncItemsTransportPainelController extends BaseController implements  Vervos
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
        if(!is_numeric($request->page)
            && !is_numeric($request->limit)
            && !$request->query('quien', null)
            && !$request->query('fehcaInicio', null)
            && !$request->query('fechaFinal', null)
            && !$request->query('evento', null)
            && !$request->query('bascula', null)) {
            return $this->handleAlert('Faltan parametros para la consulta.');
        }
        $consulta = SyncItemsTransportPaines::select(
            'ItensTransportPainel.*',
            DB::raw("e.serialnumber collate SQL_Latin1_General_CP1_CI_AS as placa"),
            DB::raw("case StartEnd when 'E' then l1.LocationDesc else l.LocationDesc end as provedor"),
            DB::raw("case StartEnd when 'E' then l.LocationDesc else l1.LocationDesc end as destino"),
            DB::raw("m.MSODesc AS producto"),
            DB::raw("pe.peso as peso1"),
            DB::raw("ItensTransportPainel.Peso*1000 as peso3"),
            DB::raw("em.FirstName+' '+em.LastName AS quien"),
            DB::raw("case StartEnd WHEN 'E' then 'ENTRADA' else 'SALIDA' end as tipo"),
            DB::raw("convert(varchar,ItensTransportPainel.TranDate,106)+' '+convert(varchar,ItensTransportPainel.ClockTime,108) as fecha"),
        )
        ->leftJoin('TimeScanSI.dbo.jobs as j', 'ItensTransportPainel.jobid', '=', DB::raw('j.JobId collate SQL_Latin1_General_CP1_CI_AS'))
        ->leftJoin('TimeScanSI.dbo.mso as m', 'ItensTransportPainel.MSOID', '=', DB::raw('m.MSOID collate SQL_Latin1_General_CP1_CI_AS'))
        ->leftJoin('TimeScanSI.dbo.Location as l', 'ItensTransportPainel.LocationID', '=', DB::raw('l.LocationID collate SQL_Latin1_General_CP1_CI_AS'))
        ->leftJoin('TimeScanSI.dbo.Location as l1', 'ItensTransportPainel.remoteLocationID', '=', DB::raw('l1.LocationID collate SQL_Latin1_General_CP1_CI_AS'))
        ->leftJoin('TimeScanSI.dbo.Equipments as e', 'ItensTransportPainel.EquipmentID', '=', DB::raw('e.EquipmentID collate SQL_Latin1_General_CP1_CI_AS'))
        ->leftJoin('TimeScanSI.dbo.Employees as em', 'ItensTransportPainel.ForemanID', '=', DB::raw('em.EmployeeID collate SQL_Latin1_General_CP1_CI_AS'))
        ->leftJoin('BDsolicitudes.dbo.sync_relacion_VehiculosPesos as pe', 'ItensTransportPainel.EquipmentID', '=', DB::raw('pe.vehiculo collate SQL_Latin1_General_CP1_CI_AS'))
        ->whereBetween('ItensTransportPainel.TranDate', [$request->fechaInicio, $request->fechaFinal])
        ->where('em.JobID', 'basc')
        ->whereNull('ItensTransportPainel.ScanDevice')
        ->orderBy('ItensTransportPainel.ClockTime', 'desc');
        if ($request->quien != 0) {
            $consulta = $consulta->where('em.EmployeeID', $request->quien);
        }
        if ($request->evento != '-1') {
            if ($request->evento == 2) {
                $consulta = $consulta->where(function ($query){
                    $query->orWhere('ItensTransportPainel.StartEnd','S');
                    $query->orWhere('ItensTransportPainel.StartEnd', 'E');
                });

            } else {
                $consulta = $consulta->where('ItensTransportPainel.StartEnd', $request->evento);
            }
        }
        $contador = clone $consulta;
        $contador = $contador->select('TXCounter')->get();
        $rows = $contador->count();
        $limitePaginas = ($rows/$request->limit) + 1;
        $consulta = $consulta->forPage($request->page, $request->limit)->get();

        return $this->handleResponse($request, $this->itemTransportePanelToArray($consulta),  __("messages.consultado"), $limitePaginas);
    }

    public function basculaMovil(Request $request) {
        if(
            !$request->query('fehcaInicio', null)
            && !$request->query('fechaFinal', null)) {
            return $this->handleAlert('Faltan parametros para la consulta.');
        }
        $consulta = SyncItemsTransportPaines::select(
            DB::raw("em.FirstName+' '+em.LastName  ALV, count(*) TT")
        )
            ->leftJoin('TimeScanSI.dbo.jobs as j', 'ItensTransportPainel.jobid', '=', DB::raw('j.JobId collate SQL_Latin1_General_CP1_CI_AS'))
            ->leftJoin('TimeScanSI.dbo.Employees as em', 'ItensTransportPainel.ForemanID', '=', DB::raw('em.EmployeeID collate SQL_Latin1_General_CP1_CI_AS'))
            ->whereBetween('ItensTransportPainel.TranDate', [$request->fechaInicio, $request->fechaFinal])
            ->whereNull('ItensTransportPainel.ScanDevice')->get();
        return $this->handleResponse($request, $consulta,  __("messages.consultado"));
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {

    }

}
