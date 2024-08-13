<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\LogAllTable;
use Exception;
use Illuminate\Http\Request;

class LogAllTableController extends BaseController implements Vervos
{
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
        try {
            $page = $request->page;
            $limit = $request->limit;
            $consulta = LogAllTable::select('LogAllTable.*');
            $consulta = $consulta->forPage($page, $limit)->get();
            $rows = LogAllTable::select('id_log')->count();
            $limitePaginas = $rows/$limit;
            if(fmod($limitePaginas, 1) !== 0.00) {
                $limitePaginas ++;
            }
            return $this->handleResponse($request, $this->logAllTableToArray($consulta), __("messages.consultado"), $limitePaginas);
        } catch (Exception $exc) {
            return response($exc);
        }
    }

    public function getPorProyecto(Request $request, $proyecto)
    {

    }

}
