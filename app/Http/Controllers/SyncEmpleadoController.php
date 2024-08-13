<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\SyncEmpleados;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncEmpleadoController extends BaseController implements  Vervos
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
        if(!is_numeric($request->page) || !is_numeric($request->limit)) {
            return $this->handleAlert('Faltas datos en la consulta');
        }
        $consulta = SyncEmpleados::where('jobid', 'basc')
            ->orderBy('FirstName', 'asc')
            ->orderBy('LastName', 'asc');
        $contador = clone $consulta;
        $contador = $contador->select('EmployeeID')->get();
        $rows = $contador->count();
        $limitePaginas = ($rows/$request->limit) + 1;
        $consulta = $consulta->forPage($request->page, $request->limit)->get();
        return $this->handleResponse($request, $this->syncEmpleadoToArray($consulta), __("messages.consultado"), $limitePaginas);
    }

    public function empleadosParaViajeBascula(Request $request, $cedula) {
        if(
            !is_numeric($request->page)
            || !is_numeric($request->limit)
        ) {
            return $this->handleAlert('Faltan parametros para la consulta.');
        }
        $consulta = SyncEmpleados::select(
            'EmployeeID as CEDULA',
            'FirstName as NOMBRE',
            'LastName as APELLIDO',
            'Status as ESTADO'
        );
        if (strlen($cedula) > 0 && strcmp($cedula, 'null') != 0) {
            $consulta = $consulta->where(function ($query) use ($cedula) {
                $cedula = '%'.$cedula.'%';
                $query->where(DB::raw("convert(varchar(50),EmployeeID)"), 'like', $cedula)
                ->orWhere('LastName', 'like', $cedula)
                ->orWhere('LastName', 'like', $cedula);
            });
        }
        $consulta = $consulta->where('EmployeeType', 'o');
        $contador = clone $consulta;
        $contador = $contador->select('EmployeeID')->get();
        $rows = $contador->count();
        $limitePaginas = 1;

        if ($rows > 0) {
            $limitePaginas = ($rows/$request->limit) + 1;
        }
        $consulta = $consulta->forPage($request->page, $request->limit)->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"), $limitePaginas);
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
