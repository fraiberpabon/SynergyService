<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\WbTipoCalzada;
use Illuminate\Http\Request;
use Validator;
use DB;

class WbTipoCalzadaController extends BaseController implements Vervos
{
    /**
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
        $validator = Validator::make($req->all(), [
            'calzada' => 'required|string',
            'descripcion' => 'required|string'
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Consultamos los permisos
        $usuario = $this->traitGetMiUsuarioProyectoPorId($req);

        //consultamos el permiso de crear en el rol
        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('TIPO_CALZADA_CREAR', $usuario->fk_rol);

        //consultamos si tiene el permiso
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }

        //eliminamos los espacios
        $calzada = strtolower(str_replace(' ', '', $req->calzada));
        //luego consultamos la calzada.
        $existe = WbTipoCalzada::whereRaw("LOWER(REPLACE(Calzada, ' ', '')) = ?", [$calzada]);
        $existe = $this->filtrar($req, $existe)->exists();

        //consultamos si existe el registro
        if ($existe) {
            //si el registro existe devolvemos el error
            return $this->handleAlert(__('messages.ops_tipo_cazada_ya_existe'), false);
        }


        //creamos nueva instacia del modelo
        $modelo = new WbTipoCalzada;

        //establecemos el proyecto y compañia
        $modelo = $this->traitSetProyectoYCompania($req, $modelo);

        $modelo->Calzada = $req->calzada;
        $modelo->Descripcion = $req->descripcion;
        $modelo->userCreator = $usuario->fk_usuario;
        $modelo->dateCreate = DB::raw('SYSDATETIME()');
        $modelo->Estado = 'A';

        if (!$modelo->save()) {
            return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
        }
        return $this->handleAlert(__('messages.registro_exitoso'), true);
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
     * @param $id
     * @return void
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function get(Request $request)
    {
        $consulta = WbTipoCalzada::select();
        if ($request->estado) {
            $consulta->where('Estado', $request->estado);
        }

        //consultamos si existe filtro de busqueda
        if ($request->has('ask')) {
            $busqueda = $request->ask;
            $consulta->where(function ($query) use ($busqueda) {
                $query->orWhere('Calzada', 'like', '%' . $busqueda . '%')
                    ->orWhere('Descripcion', 'like', '%' . $busqueda . '%');
            });
        }

        $limitePagina = 1;

        if (($request->has('limit') && is_numeric($request->limit)) && ($request->has('page') && is_numeric($request->page))) {
            $consulta = $this->filtrarPorProyecto($request, $consulta);

            //clonamos la consulta para hacer cambios sin alterar la consulta principal.
            $contador = clone $consulta;
            //obtenemos el total de registros.
            $contador = $contador->count();
            //dividimos por paginas, esto agregando la pagina y el limite que vienen por parte del cliente.
            $consulta = $consulta->forPage($request->page, $request->limit)->get(['id_tipo_calzada','Calzada','Descripcion','Estado']);
            //redondeamos para arriba el valor de la divicion.
            $limitePagina = ceil($contador / $request->limit);
            //si el limite es menor a 1 entonces hacemos que el limite de pagina sea 1.
            if ($limitePagina < 1) {
                $limitePagina = $limitePagina = 1;
            }
        } else {
            $consulta = $this->filtrarPorProyecto($request, $consulta)->get();
        }


        return $this->handleResponse($request, $this->wbTipoCalzadaToArray($consulta), __("messages.consultado"), $limitePagina);
    }

    public function getActivos(Request $request)
    {
        $consulta = WbTipoCalzada::where('Estado', 'A');
        $consulta = $this->filtrar($request, $consulta)->get();
        return $this->handleResponse($request, $this->wbTipoCalzadaToArray($consulta), __("messages.consultado"));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function editar(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'identificador' => 'required|numeric',
            'calzada' => 'required|string',
            'descripcion' => 'required|string'
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Consultamos los permisos
        $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('TIPO_CALZADA_EDITAR', $usuarioRol->fk_rol);
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }

        //Buscamos el registro por medio de su ID
        $modelo = WbTipoCalzada::find($req->identificador);

        //En caso de no encontrarlo se devuelve el error
        if ($modelo == null) {
            return $this->handleAlert(__('messages.tipo_calzada_no_encontrado'), false);
        }

        //En caso de encontrarlo se realizan las modificaciones
        $modelo->Calzada = $req->calzada;
        $modelo->Descripcion = $req->descripcion;
        $modelo->userUpdate = $usuarioRol->fk_usuario;
        $modelo->dateUpdate = DB::raw('SYSDATETIME()');

        //Guardamos los cambios, en caso de error devolvemos el error
        if (!$modelo->save()) {
            return $this->handleAlert(__('messages.no_se_pudo_realizar_el_registro'), false);
        }
        return $this->handleAlert(__('messages.registro_exitoso'), true);
    }

    public function eliminar(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'identificador' => 'required|numeric'
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Consultamos los permisos
        $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('TIPO_CALZADA_ADMIN', $usuarioRol->fk_rol);
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }

        //Buscamos el registro por medio de su ID
        $modelo = WbTipoCalzada::find($req->identificador);

        //En caso de no encontrarlo se devuelve el error
        if ($modelo == null) {
            return $this->handleAlert(__('messages.tipo_calzada_no_encontrado'), false);
        }

        //En caso de encontrarlo se cambia su estado a inactivo
        $modelo->estado = 'I';
        $modelo->userUpdate = $usuarioRol->fk_usuario;
        $modelo->dateUpdate = DB::raw('SYSDATETIME()');

        //Guardamos los cambios, en caso de error devolvemos el error
        if (!$modelo->save()) {
            return $this->handleAlert(__('messages.error_al_intentar_hacer_cambios'), false);
        }
        return $this->handleAlert(__('messages.registro_eliminado'), true);
    }

    public function cambiarEstado(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'identificador' => 'required|numeric'
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Consultamos los permisos
        $usuarioRol = $this->traitGetMiUsuarioProyectoPorId($req);

        $permiso = $this->traitGetPermisosPorNombrePermisoYRolActivo('TIPO_CALZADA_CAMBIAR_ESTADOS', $usuarioRol->fk_rol);
        if (count($permiso) == 0) {
            return $this->handleAlert(__('messages.no_tiene_los_permisos_necesarios_para_realizar_esta_accion'), false);
        }

        //Buscamos el registro por medio de su ID
        $modelo = WbTipoCalzada::find($req->identificador);

        //En caso de no encontrarlo se devuelve el error
        if ($modelo == null) {
            return $this->handleAlert(__('messages.tipo_calzada_no_encontrado'), false);
        }

        //En caso de encontrarlo se cambia su estado a inactivo
        if ($modelo->Estado == 'I') {
            $modelo->Estado = 'A';
        } else {
            $modelo->Estado = 'I';
        }
        $modelo->userUpdate = $usuarioRol->fk_usuario;
        $modelo->dateUpdate = DB::raw('SYSDATETIME()');

        //Guardamos los cambios, en caso de error devolvemos el error
        if (!$modelo->save()) {
            return $this->handleAlert(__('messages.error_al_intentar_hacer_cambios'), false);
        }
        return $this->handleAlert(__('messages.registro_cambio_de_estado'), true);
    }
}
