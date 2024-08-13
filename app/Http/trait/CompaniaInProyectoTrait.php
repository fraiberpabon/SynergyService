<?php

namespace App\Http\trait;

use App\Models\WbCompanieProyecto;

trait CompaniaInProyectoTrait
{
    /**
     * @param $proyecto
     * @param $compania
     * @return bool
     * Mi empresa default pertenece al proyecto actual y es principal del proyecto
     */
    public function traitExisteEmpresaEnProyecto($proyecto, $compania) {
        $companiaProyecto = WbCompanieProyecto::where('fk_compañia', $compania)
            ->where('fk_id_project_Company', $proyecto)
            ->get();
        return $companiaProyecto->count() > 0;
    }

    /**
     * @param $proyecto
     * @param $compania
     * @return bool
     * Consulta si la empresa es principal en el proyecto
     */
    public function traitEmpresaPorProyectoEsPrincipal($proyecto, $compania) {
        return WbCompanieProyecto::where('fk_compañia', $compania)
                ->where('fk_id_project_Company', $proyecto)
                ->where('compania_principal', 1)
                ->get()
                ->count() > 0;
    }
}
