<?php

namespace App\Http\trait;

use App\Models\estado;

trait EstadoModelTrait
{
    public function traitEstadoTerminado() {
        $estadoTerminado = estado::where('descripcion_estado', 'TERMINADO')->get();
        return $estadoTerminado[0];
    }
}
