<?php

namespace App\Http\Controllers;

use App\Http\Resources\Wb_Liberaciones_Reponsable_R;
use App\Models\Wb_Liberaciones_Reponsable_M;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class Wb_Liberaciones_Reponsable extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return false|string
     */

    public function getResponsableDeprecated(Request $request, $id)
    {
        $Actividades = Wb_Liberaciones_Reponsable_M::select(
            'id_liberacion_responsable'
            , 'fk_id_liberaciones_actividades'
            , 'Area.Area'
            , 'fk_id_area'
            , 'Wb_Liberaciones_Reponsable.estado')
            ->leftjoin('Area', 'Wb_Liberaciones_Reponsable.fk_id_area', '=', 'Area.id_area ')
            ->where('fk_id_liberaciones_actividades', '=', $id)
            ->where('Wb_Liberaciones_Reponsable.estado', '=', 1)->get();
        return json_encode(new Wb_Liberaciones_Reponsable_R($Actividades));
    }
    //listamos los responsables de actividades
    public function getResponsable(Request $request, $id)
    {
        $actividades = Wb_Liberaciones_Reponsable_M::select(
            'id_liberacion_responsable'
            , 'fk_id_liberaciones_actividades'
            , 'Area.Area'
            , 'fk_id_area'
            , 'Wb_Liberaciones_Reponsable.estado')
            ->leftjoin('Area', 'Wb_Liberaciones_Reponsable.fk_id_area', '=', 'Area.id_area ')
            ->where('fk_id_liberaciones_actividades', '=', $id)
            ->where('Wb_Liberaciones_Reponsable.estado', '=', 1);
        $actividades = $this->filtrar($request, $actividades, 'Wb_Liberaciones_Reponsable')->get();
        return $this->handleResponse($request, $this->liberacionesResponsableToArray($actividades), __('messages.consultado'));
    }

    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
