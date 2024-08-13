<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Http\Resources\SolicitudAsfaltoCollection;
use App\Models\WbSolitudAsfalto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsfaltoController extends BaseController implements Vervos
{
    /**
     * Display a listing of the resource.
     *
     * @return false|string
     */
    public function index(Request $request)
    {
        $consulta = WbSolitudAsfalto::select(
            'id_solicitudAsf',
            'SolitudAsfalto.fk_id_usuario',
            'nombreCompañia',
            'fechaSolicitud',
            'formula',
            'abscisas',
            'hito',
            'tramo',
            'calzada',
            'cantidadToneladas',
            'tipoMezcla',
            'FechaHoraProgramacion',
            'SolitudAsfalto.estado',
            'observaciones',
            'CompañiaDestino as companiDestino',
            'fechaAceptacion',
            'usuarioss.Nombre',
            'usuarioss.Apellido',
            'toneFaltante',
            'CostCode',
            'Correo',
            'fk_LocationID',
            'TimeScanSI.dbo.MSO.MSOID')
            ->leftjoin('usuarioss', 'SolitudAsfalto.fk_id_usuario', '=', 'usuarioss.id_usuarios')
            ->leftjoin('usuPlanta', 'SolitudAsfalto.CompañiaDestino', '=', 'usuPlanta.NombrePlanta')
            ->leftjoin('TimeScanSI.dbo.MSO', 'MSODesc', 'like', DB::raw("'%'+REPLACE(REPLACE([formula], 'mm', ''), ' ', '')+'%' COLLATE SQL_Latin1_General_CP1_CS_AS"))
            ->orderBy('SolitudAsfalto.id_solicitudAsf', 'desc');
        if ($request->query('id', null) && is_numeric($request->id)) {
            $consulta = $consulta->where('SolitudAsfalto.id_solicitudAsf', $request->id);
        }
        if ($request->query('estado', null) && strlen($request->estado) > 0) {
            $consulta = $consulta->where('SolitudAsfalto.estado', $request->estado);
        }
        if ($request->planta && strlen($request->planta) > 0) {
            $like = '%'.strtolower($request->planta).'%';
            $consulta = $consulta->whereRaw("LOWER(SolitudAsfalto.CompañiaDestino) like (?)", $like);
        }
        if ($request->fechaProgramacion && strlen($request->fechaProgramacion) > 0) {
            $consulta = $consulta->whereRaw("cast(CONVERT(VARCHAR(10), SUBSTRING(SolitudAsfalto.FechaHoraProgramacion,0, CHARINDEX(' ', SolitudAsfalto.FechaHoraProgramacion)), 103) as  date) = cast((?) as date)", $request->fechaProgramacion);
        }
        $limitePaginas = 1;
        if($request->query('page') && is_numeric($request->page) && $request->query('limit') && is_numeric($request->limit)) {
            $contador = clone $consulta;
            $contador = $this->filtrar($request, $contador, 'SolitudAsfalto');
            $contador = $contador->select('SolitudAsfalto.id_solicitudAsf')->get();
            $consulta = $consulta->forPage($request->page, $request->limit)->get();
            $limitePaginas = ($contador->count()/$request->limit) + 1;
        } else {
            $consulta =  $consulta->get();
        }
        return $this->handleResponse($request, $this->asfaltoToArray($consulta), __("messages.consultado"), $limitePaginas);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function get(Request $request)
    {
        // TODO: Implement get() method.
        $consulta = WbSolitudAsfalto::select(
            'id_solicitudAsf',
            'SolitudAsfalto.fk_id_usuario',
            'nombreCompañia',
            'fechaSolicitud',
            'formula',
            'abscisas',
            'hito',
            'tramo',
            'calzada',
            'cantidadToneladas',
            'tipoMezcla',
            'FechaHoraProgramacion',
            'SolitudAsfalto.estado',
            'observaciones',
            'CompañiaDestino as companiDestino',
            'fechaAceptacion',
            'usuarioss.Nombre',
            'usuarioss.Apellido',
            'toneFaltante',
            'CostCode',
            'Correo',
            'fk_LocationID',
            'TimeScanSI.dbo.MSO.MSOID')
            ->leftjoin('usuarioss', 'SolitudAsfalto.fk_id_usuario', '=', 'usuarioss.id_usuarios')
            ->leftjoin('usuPlanta', 'SolitudAsfalto.CompañiaDestino', '=', 'usuPlanta.NombrePlanta')
            ->leftjoin('TimeScanSI.dbo.MSO', 'MSODesc', 'like', DB::raw("'%'+REPLACE(REPLACE([formula], 'mm', ''), ' ', '')+'%' COLLATE SQL_Latin1_General_CP1_CS_AS"))
            ->orderBy('SolitudAsfalto.id_solicitudAsf', 'desc')
            ->take(200);
        $consulta = $this->filtrar($request, $consulta, 'SolitudAsfalto')->get();
        return $this->handleResponse($request, $this->asfaltoToArray($consulta), __("messages.consultado"));
        //return json_encode(new SolicitudAsfaltoCollection($consulta));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {

    }
}
