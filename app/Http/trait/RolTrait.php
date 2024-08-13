<?php

namespace App\Http\trait;

use App\Models\WbSeguriRoles;

trait RolTrait
{
    public function traitGetRolAdmin() {
        $consulta = WbSeguriRoles::where('nombreRol', 'ROL_ADMIN')->get();
        return $consulta[0];
    }

    public function traitGetRolPorIdyActivo($rol) {
        $consulta = WbSeguriRoles::where('id_Rol', $rol)->where('estado', 1)->get();
        return $consulta->count() > 0;
    }
}
