<?php

namespace App\Http\trait;

use App\Models\ProjectCompany;

trait ProyectoTrait
{
    public function traitGetAllProyetos() {
        return ProjectCompany::all();
    }

    public function traitExisteProyectoYActivo($proyecto) {
        $consulta = ProjectCompany::where('id_Project_Company', $proyecto)
        ->where('Estado', 'A')->get();
        return $consulta->count() > 0;
    }
}
