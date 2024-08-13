<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\SyncBasculas;
use Illuminate\Http\Request;

class SyncBasculasController extends BaseController implements Vervos
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

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function get(Request $request)
    {
        $consulta = SyncBasculas::select('nombre')
            ->orderBy('nombre', 'asc')
            ->where('estado', 'A')
            ->groupBy('nombre')
            ->get();
        return $this->handleResponse($request, $this->syncBasculatoArray($consulta), 'Consultado.');
    }

    public function consultarSiExisteBasculoSiNoCrearlo(Request $request) {
        if ($request->pc == null) {
            return $this->handleAlert('Pc no valida');
        }
        $consulta= SyncBasculas::select()
            ->where('pc', $request->pc)
            ->where('estado'. 'A')
            ->first();
        if ($consulta) {
            return $this->handleResponse($request, [], 'Consultado');
        } else {
            $consulta = SyncBasculas::select()
                ->where('pc', $request->pc)
                ->first();
            if (!$consulta) {
                $modelo = new SyncBasculas;
                $modelo->pc = $request->pc;
                $modelo->estado = 'E';
                $modelo->save();
            }
            return $this->handleAlert('Sin permiso');
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
