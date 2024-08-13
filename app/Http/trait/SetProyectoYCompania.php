<?php

namespace App\Http\trait;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait SetProyectoYCompania
{
    use TokenHelpersTrait, CompaniaTrait;

    /**
     * @param $usuario
     * @param $proyecto
     * @return Model
     * Busca por usuario su proyecto que tiene asignado como default, si este se encuentra y el proyecto no esta bloqueado
     * lo devuelve, en otro caso si es null busca en los demas proyectos el primero que encuentre
     */
    public function traitSetProyectoYCompania(Request $request, $miModel)
    {
        $miModel->fk_id_project_Company = $this->traitGetProyectoCabecera($request);
        $miModel->fk_compañia = $this->traitIdEmpresaPorProyecto($request);
        return $miModel;
    }

    public function traitSetProyectoYCompania2(Request $request, Model $miModel)
    {
        $miModel->fk_id_project_company = $this->traitGetProyectoCabecera($request);
        $miModel->fk_compania = $this->traitIdEmpresaPorProyecto($request);
        return $miModel;
    }

    public function traitSetProyecto(Request $request, Model $miModel)
    {
        $miModel->fk_id_project_company = $this->traitGetProyectoCabecera($request);
        return $miModel;
    }


}
