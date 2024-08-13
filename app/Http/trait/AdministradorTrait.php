<?php

namespace App\Http\trait;

use App\Models\usuarios_M;

trait AdministradorTrait
{
    function traitGetAdministrador() {
        $consulta = usuarios_M::where('usuario', 'administrador')->get();
        return $consulta[0];
    }
}
