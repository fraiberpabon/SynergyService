<?php

namespace App\Http\trait;

use App\Models\Usuarios\usuarios_M;
use App\Models\Wb_Seguri_Permiso;
use App\Models\Roles\WbSeguriRolesPermiso;
use App\Models\Usuarios\WbUsuarioProyecto;
use http\Env\Request;

trait PermisoModelTrait
{
    //super permiso
    var $permiso_super = 'R234';
    public function traitPermisoPorNombre($nombre) {
        $permiso = Wb_Seguri_Permiso::where('nombrePermiso', $nombre)->get();
        return $permiso;
    }

    public function traitPermisosPorIdYRol($rol, $permiso) {
        $permiso = WbSeguriRolesPermiso::where('fk_id_Rol', $rol)->where('fk_id_permiso', $permiso)->get();
        return $permiso;
    }

    public function traitPermisosPorRol($rol) {
        return WbSeguriRolesPermiso::select('Wb_Seguri_Permisos.id_permiso as identificador', 'Wb_Seguri_Permisos.nombrePermiso as nombre')
            ->leftjoin('Wb_Seguri_Permisos', 'Wb_Seguri_Permisos.id_permiso', '=', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
            ->where('Wb_Seguri_Roles_Permisos.fk_id_Rol','=',$rol)
            ->get();
    }

    public function traitGetPermisosPorusuarioActivo($proyecto, $usuario) {
        return WbUsuarioProyecto::select('Wb_Seguri_Permisos.id_permiso as identificador', 'Wb_Seguri_Permisos.nombrePermiso as nombre')
            ->leftjoin('Wb_Seguri_Roles_Permisos', 'Wb_Seguri_Roles_Permisos.fk_id_Rol', '=', 'Wb_Usuario_Proyecto.fk_rol')
            ->leftjoin('Wb_Seguri_Permisos', 'Wb_Seguri_Permisos.id_permiso', '=', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
            ->where('Wb_Usuario_Proyecto.fk_usuario','=',$usuario)
            ->where('Wb_Usuario_Proyecto.fk_id_project_Company','=',$proyecto)
            ->get();
    }


    public function traitGetPermisosPorNombrePermisoYRolActivo($permiso, $rol) {
        return  WbSeguriRolesPermiso::select(
            'Wb_Seguri_Permisos.nombrePermiso as nombre',
        )->leftjoin('Wb_Seguri_Permisos', 'Wb_Seguri_Permisos.id_permiso', '=', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
            ->where('Wb_Seguri_Permisos.nombrePermiso', '=', $permiso)
            ->where('Wb_Seguri_Roles_Permisos.fk_id_Rol', $rol)
            ->get();
    }
}
