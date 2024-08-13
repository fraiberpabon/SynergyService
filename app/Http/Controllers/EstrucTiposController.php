<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\estruc_tipos;
use Illuminate\Http\Request;

class EstrucTiposController extends BaseController  implements Vervos {

    public function get(Request $request) {
        $consulta = estruc_tipos::select();
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->estructTiposToArray($consulta), 'Consultado.');
    }

    public function getByHito(Request $request, $hito) {
        $consulta = estruc_tipos::select(
            'Estruc_tipos.id',
            'Estruc_tipos.TIP_1',
            'Estruc_tipos.TIPO_DE_ESTRUCTURA',
            'Estruc_tipos.actividad',
        )
            ->leftjoin('Estructuras', 'Estructuras.fk_tipo_estructura', '=', 'Estruc_tipos.id')
            ->where('Estructuras.HITO_OTRO_SI_10', $hito)
            ->orderby('Estruc_tipos.TIPO_DE_ESTRUCTURA')
            ->groupby('Estruc_tipos.id')
            ->groupby('Estruc_tipos.TIP_1')
            ->groupby('Estruc_tipos.TIPO_DE_ESTRUCTURA')
            ->groupby('Estruc_tipos.actividad');
        $consulta = $this->filtrar($request, $consulta, 'Estruc_tipos')->get();
        return $this->handleResponse($request, $this->estructTiposToArray($consulta), __("messages.consultado"));
    }

    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
