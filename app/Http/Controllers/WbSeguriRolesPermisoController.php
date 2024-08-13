<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbSeguriRolesPermiso;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WbSeguriRolesPermisoController extends BaseController implements Vervos
{
    public function delete(Request $request, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Permiso de rol no valido.', false);
        }
        if(WbSeguriRolesPermiso::find($id) == null) {
            return $this->handleAlert('Permiso de rol no encontrado.', false);
        }
        try {
            DB::table('Wb_Seguri_Roles_Permisos')
                ->where('id_roles_permisos', $id)
                ->delete();
            return $this->handleResponse($request, [], 'Permiso de rol eliminado.');
        } catch(Exception $exc) {}
        return $this->handleAlert('Permiso de rol no eliminado.', false);
    }

    /**
     * @param Request $req
     * @return void
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    /**
     * @param Request $req
     * @param $id
     * @return void
     */
    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    /**
     * @return void
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
