<?php

namespace App\Http\Controllers;


use App\Http\interfaces\Vervos;
use App\Models\Compania;
use App\Models\Usuarios\usuarios_M;
use App\Models\Roles\WbSeguriRoles;
use App\Models\Usuarios\WbUsuarioProyecto;
use Exception;
use Illuminate\Http\Request;

class WbUsuarioProyectoController extends BaseController implements Vervos
{

    public function post(Request $req)
    {
        // TODO: Implement post() method.
        if (!$req->json()->has('usuario')) {
            return $this->handleAlert('Falta el nombre del tramo');
        }
        if(!$req->has('proyecto')) {
            return $this->handleAlert('Falta la descripcion del tramo');
        }
        if (!$req->has('compania')) {
            return $this->handleAlert('Falta el estado del tramo');
        }
        if (!$req->has('rol')) {
            return $this->handleAlert('Falta el estado del tramo');
        }
        if (usuarios_M::find($req->usuario) == null) {
            return $this->handleAlert('Usuario no encontrado.');
        }
        if (WbUsuarioProyecto::find($req->proyecto) == null) {
            return $this->handleAlert('Proyecto no encontrado.');
        }
        if (Compania::find($req->compania) == null) {
            return $this->handleAlert('Compañia no encontrada.');
        }
        if (WbSeguriRoles::find($req->rol) == null) {
            return $this->handleAlert('Rol no encontrado.');
        }
        if (WbUsuarioProyecto::where('fk_usuario', $req->usuario)->where('fk_id_project_Company')->where('eliminado', 0)->first()) {
            return $this->handleResponse($req, [], 'El usuario ya se encuentra asignado al propyecto');
        }
        $modelo = new WbUsuarioProyecto;
        $modelo->fk_usuario = $req->usuario;
        $modelo->fk_id_project_Company = $req->proyecto;
        $modelo->fk_compañia = $req->compania;
        $modelo->fk_rol = $req->rol;
        $increment = WbUsuarioProyecto::where('fk_usuario', $req->usuario)->where('fk_id_project_Company')->count();
        $modelo->increment = $increment + 1;
        try {
            if ($modelo->save()) {
                return $this->handleResponse($req, [], 'Usuario asignado al proyecto');
            }
        } catch (Exception $exc) {
            printf($exc->getMessage());
        }
        return $this->handleAlert('Ocurrio un error mientras se asignaba el usuario a un proyecto, si el error persiste consulte con el administrador.');
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function desAsignar(Request $request, $proyecto, $usuario)
    {
        if (!is_numeric($usuario)) {
            return $this->handleAlert('Usuario no valido');
        }
        if (!is_numeric($proyecto)) {
            return $this->handleAlert('Proyecto no valido');
        }
        $usuarioProyecto = WbUsuarioProyecto::where('fk_usuario', $usuario)->where('fk_id_project_Company', $proyecto)->first();
        if ($usuarioProyecto === null) {
            return $this->handleAlert('Asignacion no encontrada');
        }
        $usuarioProyecto->eliminado = 1;
        try {
            if ($usuarioProyecto->save()) {
                return $this->handleResponse($request, [], 'Usuario desasignado de proyecto');
            }
        } catch (Exception $exc) {}
        return $this->handleAlert('Ocurrio un error mientras se desasignaba el usuario del proyecto, si el error persiste consulte con el administrador.');
    }

    public function delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Falta el nombre del tramo');
        }
        $usuarioProyecto = WbUsuarioProyecto::find($id);
        if ($usuarioProyecto === null) {
            return $this->handleAlert('Asignacion no encontrada');
        }
        $usuarioProyecto->eliminado = 1;
        try {
            if ($usuarioProyecto->save()) {
                return $this->handleResponse($request, [], 'Usuario desasignado de proyecto');
            }
        } catch (Exception $exc) {}
        return $this->handleAlert('Ocurrio un error mientras se desasignaba el usuario del proyecto, si el error persiste consulte con el administrador.');
    }

    public function get(Request $request)
    {

        $roles = WbSeguriRoles::all();
        $empresas = Compania::all();
        $consulta = WbUsuarioProyecto::select('Wb_Usuario_Proyecto.*');
        if ($request->query->has('usuario')) {
            $consulta->where('fk_usuario', $request->query->get('usuario'));
        }
        $consulta->get();
        foreach ($consulta as $item) {
            $this->setRolById($item, $roles);
            $this->setEmpresaById($item, $empresas);
        }
        return $this->handleResponse($request, $this->wbUsuarioProyectoArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function getByUsuario(Request $request, $usuario) {
        $roles = WbSeguriRoles::all();
        $empresas = Compania::all();
        $consulta = WbUsuarioProyecto::where('fk_usuario', $usuario)->get();
        foreach ($consulta as $item) {
            $this->setRolById($item, $roles);
            $this->setEmpresaById($item, $empresas);
        }
        return $this->handleResponse($request, $this->wbUsuarioProyectoArray($consulta), __("messages.consultado"));
    }

    private function setRolById($modelo, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($modelo->fk_rol == $array[$i]->id_Rol) {
                $reescribir= $this->wbSeguriRolesToModel($array[$i]);
                $modelo->objectRol = $reescribir;
                break;
            }
        }
    }

    private function setEmpresaById($estructura, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($estructura->fk_compañia == $array[$i]->id_compañia) {
                $reescribir= $this->companiaToModel($array[$i]);
                $estructura->objectEmpresa = $reescribir;
                break;
            }
        }
    }
}
