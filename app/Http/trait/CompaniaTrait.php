<?php

namespace App\Http\trait;

use App\Http\Controllers\WbUsuarioProyectoController;
use App\Models\Usuarios\WbUsuarioProyecto;
use Illuminate\Http\Request;

trait CompaniaTrait
{
    use TokenHelpersTrait;
    /**
     * @param $proyecto
     * @param $compania
     * @return bool
     * Mi empresa default pertenece al proyecto actual y es principal del proyecto
     */
    public function traitIdEmpresaPorProyecto(Request $request)
    {
        $proyecto = $this->traitGetProyectoCabecera($request);
        $usuario = $this->traitGetIdUsuarioToken($request);
        $consulta = WbUsuarioProyecto::where('fk_id_project_Company', $proyecto)
            ->where('fk_usuario', $usuario)
            ->get();
        if ($consulta->count() > 0) {
            return $consulta[0]->fk_compañia;
        }
        return null;
    }
}
