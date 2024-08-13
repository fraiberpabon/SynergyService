<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\HtrUsuarios;
use App\Models\ProjectCompany;
use App\Models\usuarios_M;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class HtrUsuariosController extends BaseController implements Vervos
{
    var $acciones = array(
        "CELULAR NO REGISTRADO",
        "INTENTO DESDE DISPOSITIVO NO REGISTRADO",
        "CONTRASEÑA INCORRECTA",
        "USUARIO NO EXISTE",
        "USUARIO BLOQUEADO",
        "USUARIO INACTIVO",
        "USUARIO SIN PROYECTO VIGENTE"
    );
    public function post(Request $req)
    {
        if (!usuarios_M::where('usuario',$req->nomUsuario )) {
            return $this->handleCod(__("messages.usuario_no_encontrado"), $this->usuarioNoExisteError);
        }
        $modelo = new HtrUsuarios;
        $modelo->imei = $req->imei;
        $modelo->fechaHoraIngreso = date("Y-m-d H:i:s");
        $modelo->ip = $this->getIp();
        $modelo->accionRealizada = $req->accionRealizada;
        $modelo->nomUsuario = $req->nomUsuario;
        $modelo->Ubicacion = $req->Ubicacion;
        if (ProjectCompany::find($req->proyecto)) {
            $modelo->fk_id_project_Company = $req->proyecto;
        }
        /**
         * no hay como capturar el proyecto del usuario
         */
        //$modelo = $this->traitSetProyectoYCompania($req, $modelo);
        try {
            if ($modelo->save()) {
                return $this->handleResponse($req, [], __("messages.accion_registrada"));
            }
        } catch (\Exception $exc) {
            return $this->handleCod( __("messages.accion_no_registrada"), $this->accionNoRegistradaInicioSessionError);
        }
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
        $page = $request->page;
        $limit = $request->limit;
        $consulta = HtrUsuarios::select('htrlUsuarios.*');
        $consultaCount = HtrUsuarios::select('id_htrUsuarios');
        $consulta = $this->filtrar($request, $consulta)->orderBy('id_htrUsuarios');
        $consultaCount = $this->filtrar($request, $consultaCount);
        $consulta = $consulta->forPage($page, $limit)->get();
        $rows = $consultaCount->get()->count();
        $limitePaginas = ($rows/$limit) + 1;
        return $this->handleResponse($request, $this->htrUsuarioToArray($consulta), __("messages.consultado"), $limitePaginas);
    }

    public function getPorProyecto(Request $request, $proyecto)
    {

    }


}
