<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Http\trait\DateHelpersTrait;
use App\Models\SyncRegistro;
use App\Models\SyncRelacionVehiculoPeso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncRegistroController extends BaseController implements Vervos
{
    //
    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $baucher)
    {
        $idUsuario = $this->traitGetIdUsuarioToken($request);
        $comprobacion = 0;
        if (!($baucher == '' || strlen($baucher) == 0)) {
            $syncRegistro = SyncRegistro::where('baucher', $baucher)->first();
            $syncRegistro->fechacambio = $this->traitGetDateTimeNow();
            $syncRegistro->USERR = $idUsuario;
            if ($request->equipo != $syncRegistro->equipo) {
                $synRelacionPeso = SyncRelacionVehiculoPeso::where('VEHICULO', $syncRegistro->equipo)->first();
                $np1 = $synRelacionPeso->peso;
                $np2 = $syncRegistro->peso2;

                if (strlen($np1) > 1) {
                    $np3 = (int) $np2 - (int) $np1;
                    $obs1 = $syncRegistro->observacion . '::/::MOD: EQUIPO';
                    $syncRegistro->equipo = $request->equipo;
                    $syncRegistro->peso1 = $np1;
                    $syncRegistro->peso3 = $np3;
                    $syncRegistro->observacion = $obs1;
                    $syncRegistro->save();
                    $comprobacion = 1;
                }
            }

            if ($request->conductor != $syncRegistro->conductor) {
                $obs1 = $syncRegistro->observacion . '::/::MOD: CONDUCTOR';
                $syncRegistro->conductor = $request->conductor;
                $syncRegistro->observacion = $obs1;
                $syncRegistro->save();
                $comprobacion = 1;
            }

            if ($request->producto != $syncRegistro->producto) {
                $obs1 = $syncRegistro->observacion . '::/::MOD: PRODUCTO';
                $syncRegistro->producto = $request->producto;
                $syncRegistro->observacion = $obs1;
                $syncRegistro->save();
                $comprobacion = 1;
            }

            if ($request->origen != $syncRegistro->provedor) {
                $obs1 = $syncRegistro->observacion . '::/::MOD: PROVEDOR';
                $syncRegistro->provedor = $request->origen;
                $syncRegistro->costo = $request->costo;
                $syncRegistro->frente = $request->frente;
                $syncRegistro->observacion = $obs1;
                $syncRegistro->save();
                $comprobacion = 1;
            }

            if ($request->destino != $syncRegistro->destino) {
                $obs1 = $syncRegistro->observacion . '::/::MOD: DESTINO';
                $syncRegistro->destino = $request->destino;
                $syncRegistro->observacion = $obs1;
                $syncRegistro->save();
                $comprobacion = 1;
            }

            if ($request->tipo != $syncRegistro->tipo) {
                $obs1 = $syncRegistro->observacion . '::/::MOD: TIPO';
                $syncRegistro->tipo = $request->tipo;
                $syncRegistro->observacion = $obs1;
                $syncRegistro->save();
                $comprobacion = 1;
            }
            if ($comprobacion == 1) {
                $syncRegistro->refresh();
                $syncRegistro->intime = 'NO';
                $syncRegistro->save();
            } else {
                $syncRegistro->intime = 'NO';
                $syncRegistro->ESTADO = 'D';
                $syncRegistro->MOTIVO = strtoupper($request->motivo);
                $syncRegistro->save();
            }
            return $this->handleResponse($request, [], 'Registro actualizado');
        } else {
            return $this->handleAlert('Baucher no encontrado');
        }
    }

    public function get(Request $request)
    {
        // TODO: Implement get() method.
    }

    public function getAgrupados(Request $request)
    {
        // TODO: Implement get() method.
        $consulta = SyncRegistro::select(
            Db::raw("count(*) as VIAJES"),
            Db::raw("min(fecha) as PRIMERVIAJE"),
            Db::raw("max(fecha) as ULTIMOVIAJE"),
            Db::raw("max(fechaSistema) as SINCRONIZO"),
            Db::raw("quien as NOMBRE"),
        )
            ->whereNotIn('quien', ['', 'p.sync'])
            ->groupBy('quien')
            ->orderBy('quien')
            ->get();
        return $this->handleResponse($request, $consulta, 'Consultado');
    }

    public function mirarBascula(Request $request)
    {
        if (
            strlen($request->query('quien')) == 0
            || strlen($request->query('fechaInicio')) == 0
            || strlen($request->query('fechaFinal')) == 0
            || strlen($request->query('evento')) == 0
            || strlen($request->query('bascula')) == 0
        ) {
            return $this->handleAlert('Faltan parametros para la consulta.');
        }
        // TODO: Implement get() method.
        $consulta = SyncRegistro::from('sync_registros as reg')
            ->select(
                'reg.equipo as equipo',
                Db::raw("isnull(equ.SerialNumber,'String') as placa"),
                'conductor as cedula',
                Db::raw("isnull((emp.FirstName +' '+ emp.LastName) ,'String') as conductor"),
                Db::raw("isnull(locp.LocationDesc,(reg.provedor  collate SQL_Latin1_General_CP1_CI_AS)) as provedor"),
                Db::raw("isnull(locd.LocationDesc,(reg.destino  collate SQL_Latin1_General_CP1_CI_AS)) as  destino"),
                Db::raw("isnull(m.MSODesc,(reg.producto  collate SQL_Latin1_General_CP1_CI_AS)) as producto"),
                'reg.observacion',
                'reg.fecha',
                'reg.peso1',
                'reg.peso2',
                'reg.peso3',
                'reg.baucher',
                'reg.quien',
                /*Db::raw("emp.FirstName+' '+emp.LastName AS quien"),*/
                Db::raw("CASE WHEN [tipo]=0 THEN 'ENTRADA'ELSE   'SALIDA'END as tipo")
            )
            ->leftJoin('Equipments as equ', DB::raw('equ.EquipmentID collate SQL_Latin1_General_CP1_CI_AS'), '=', 'reg.equipo')
            ->leftJoin('TimeScanSI.dbo.Employees as emp', DB::raw('emp.EmployeeID collate SQL_Latin1_General_CP1_CI_AS'), '=', 'reg.conductor')
            ->leftJoin('TimeScanSI.dbo.Location as locp', 'reg.provedor', '=', DB::raw('convert(varchar(20), locp.ID)'))
            ->leftJoin('TimeScanSI.dbo.Location as locd', 'reg.destino', '=', DB::raw('convert(varchar(20), locd.ID)'))
            ->leftJoin('TimeScanSI.dbo.MSO as m', 'reg.producto', '=', DB::raw("convert(int,m.MSOID)")) // MATERIAL
            ->leftJoin('sync_basculas as bas', 'reg.pc', '=', DB::raw("bas.PC collate SQL_Latin1_General_CP1_CI_AS")) // RELACION BASCULA
            ->where('reg.estado', 'a')
            ->orderBy(DB::raw("CONVERT(date, reg.fecha)"), 'desc')
            ->orderBy(DB::raw("CONVERT(time, reg.fecha)"), 'desc');
        if (!$request->fechaFinal || $request->fechaFinal == '' || ($request->fechaInicio == $request->fechaFinal)) {
            $consulta = $consulta->where(DB::raw("CONVERT(Date, reg.fecha)"), 'like', $request->fechaInicio . "%");
        } else {
            $fechaFinal = date('Y-m-d', strtotime($request->fechaFinal . ' +1 day'));
            $consulta = $consulta->whereBetween('fecha', [$request->fechaInicio, $request->fechaFinal]);
        }
        if ($request->quien != 0) {
            $consulta = $consulta->where('quien', $request->quien);
        }
        if ($request->evento != -1) {
            if ($request->evento == 2) {
                $consulta = $consulta->whereRaw("(len(reg.frente)<1 or reg.frente='null");
            } else {
                $consulta = $consulta->where('Tipo', $request->evento);
            }
        }
        if ($request->bascula != 'todas') {
            $consulta = $consulta->where('bas.nombre', $request->bascula);
        }


        $is_excel = false;
        $limitePaginas = 1;
        if (($request->page == null || !is_numeric($request->page)) || ($request->limit == null || !is_numeric($request->limit))) {
            $is_excel = true;
            $consulta = $this->getFormatReportColumns($consulta->get());
        } else {
            $contador = clone $consulta;
            $contador = $contador->select('EmployeeID')->get();
            $rows = $contador->count();

            if ($rows > 0) {
                $limitePaginas = ($rows / $request->limit) + 1;
            }

            $consulta = $consulta->forPage($request->page, $request->limit)->get();
        }

        return $this->handleResponse($request, $this->syncRegistroToArray($consulta, $is_excel), __("messages.consultado"), $limitePaginas);
    }

    private function getFormatReportColumns($array)
    {
        if ($array == null || sizeof($array) == 0) {
            return $array;
        }
        foreach ($array as $key) {
            list($fecha, $hora) = explode(' ', $key->fecha);
            $key->fecha_string = $fecha;
            $key->hora = $hora;
            //$key->hora = $fecha[1];
        }
        return $array;
    }

    function registroSegunFiltro(Request $request)
    {
        if ($request->condicion) {
            // migrar siguiente version
            $consulta = DB::select("SELECT * FROM SYNCF_VIAJE('VALE','$request->info')");
            return $this->handleResponse($request, $consulta, __("messages.consultado"));
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
