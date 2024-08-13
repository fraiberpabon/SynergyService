<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbTramos;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbTramosController extends BaseController implements Vervos
{
    public function get(Request $request)
    {
        $consulta = WbTramos::select(
            'id',
            'Id_Tramo',
            'Descripcion',
            'Estado',
            DB::raw("CONVERT(varchar,[dateCreate],22) as dateCreate")
        );

        if ($request->has('estado')) {
            $consulta = $consulta->where('Estado', $request->estado);
        }
        $consulta = $this->filtrarPorProyecto($request, $consulta)->get();
        return $this->handleResponse($request, $this->tramoToArray($consulta), __("messages.consultado"));
    }

    public function getParaSync(Request $request)
    {
        $consulta = WbTramos::select(
            'Id_Tramo as TRAMO'
        );
        $consulta = $this->filtrarPorProyecto($request, $consulta)->get();
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @deprecated
     */
    public function getTramosActivosDeprecated(Request $request)
    {
        $consulta = WbTramos::select(
            'id',
            'Id_Tramo',
            'Descripcion',
            'Estado',
            DB::raw("CONVERT(varchar,[dateCreate],22) as dateCreate")
        )->where('Estado', 'A')
            ->get();
        return $this->handleResponse($request, $this->tramoToArrayMod($consulta), __("messages.consultado"));
    }

    public function getTramosActivos(Request $request)
    {
        $consulta = WbTramos::select(
            'id',
            'Id_Tramo',
            'Descripcion',
            'Estado',
            DB::raw("CONVERT(varchar,[dateCreate],22) as dateCreate")
        )->where('Estado', 'A')
            ->get();
        return $this->handleResponse($request, $this->tramoToArray($consulta), __("messages.consultado"));
    }

    public function getById(Request $request, $id)
    {
        $consulta = WbTramos::select(
            'id',
            'Id_Tramo',
            'Descripcion',
            'Estado',
            DB::raw("CONVERT(varchar,[dateCreate],22) as dateCreate")
        )->where('Id_Tramo', $id);
        $consulta = $this->filtrarPorProyecto($request, $consulta)->get();
        return $this->handleResponse($request, $this->tramoToModel($consulta), __("messages.consultado"));
    }

    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        // TODO: Implement post() method.
        if (!$req->json()->has('nombre')) {
            return $this->handleAlert('Falta el nombre del tramo');
        }
        if (!$req->has('descripcion')) {
            return $this->handleAlert('Falta la descripcion del tramo');
        }
        if (!$req->has('estado')) {
            return $this->handleAlert('Falta el estado del tramo');
        }
        if (!($req->estado == 'A' || $req->estado == 'I')) {
            return $this->handleAlert('Estado no valido');
        }
        if (WbTramos::where('Id_Tramo', $req->nombre)->where('fk_id_project_Company', $proyecto)->first() != null) {
            return $this->handleAlert('Este tramo ya existe, ingrese otro nombre.');
        }
        $modelo = new WbTramos;
        $modelo->Id_Tramo = $req->nombre . '';
        $modelo->Descripcion = $req->descripcion;
        $modelo->Estado = $req->estado;
        $modelo->userCreator = $this->traitGetIdUsuarioToken($req);
        $modelo = $this->traitSetProyectoYCompania($req, $modelo);
        try {
            if ($modelo->save()) {
                return $this->handleResponse($req, [], 'Tramo registrado');
            }
        } catch (Exception $exc) {
            printf($exc->getMessage());
        }
        return $this->handleAlert('No se pudo registrar el tramo');
    }

    /**
     * @param Request $req
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Tramo no valido');
        }
        if (!$req->json()->has('descripcion')) {
            return $this->handleAlert('Falta el campo de descripcion');
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Falta el campo de estado');
        }
        $modelo = WbTramos::find($id);
        $proyecto = $this->traitGetProyectoCabecera($req);
        if ($modelo->fk_id_project_Company != $proyecto) {
            return $this->handleAlert('Tramo no valido');
        }
        if (!($req->estado == 'A' || $req->estado == 'I')) {
            return $this->handleAlert('Estado no valido');
        }
        $modelo->Descripcion = $req->descripcion;
        $modelo->Estado = $req->estado;
        try {
            if ($modelo->save()) {
                return $this->handleResponse($req, [], 'Tramo modificado');
            }
        } catch (Exception) {
        }
        return $this->handleAlert('Tramo no modificado');
    }

    /**
     * @param $id
     * @return void
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
