<?php

namespace App\Http\trait;

use App\Models\usuarios_M;
use App\Models\WbUsuarioProyecto;
use Illuminate\Http\Request;

trait FiltroTrait
{
    use TokenHelpersTrait, CompaniaInProyectoTrait;
    function filtrar(Request $request, $consulta, $tabla = '') {
        $proyecto = $this->traitGetProyectoCabecera($request);
        $idUsuario = $this->traitGetTokenPersonal($request)->tokenable_id;
        $miUsuario = WbUsuarioProyecto::where('fk_usuario', $idUsuario)->where('fk_id_project_Company', $proyecto)->first();
        if (strlen($tabla) > 0) {
            $tabla = $tabla.".";
        }
        //verifico si en el proyecto actual mi empresa es la principal o subcontratista
        $consulta->where($tabla.'fk_id_project_Company', $proyecto);
        /*if (!$this->traitEmpresaPorProyectoEsPrincipal($proyecto, $miUsuario->fk_compañia)) {
            $consulta->where($tabla.'fk_compañia', $miUsuario->fk_compañia);
        }*/
        return $consulta;
    }


    function filtrar2(Request $request, $consulta, $tabla = '') {
        $proyecto = $this->traitGetProyectoCabecera($request);
        $idUsuario = $this->traitGetTokenPersonal($request)->tokenable_id;
        $miUsuario = WbUsuarioProyecto::where('fk_usuario', $idUsuario)->where('fk_id_project_Company', $proyecto)->first();
        if (strlen($tabla) > 0) {
            $tabla = $tabla.".";
        }
        //verifico si en el proyecto actual mi empresa es la principal o subcontratista
        $consulta->where($tabla.'fk_id_project_company', $proyecto);
        if (!$this->traitEmpresaPorProyectoEsPrincipal($proyecto, $miUsuario->fk_compañia)) {
            $consulta->where($tabla.'fk_compañia', $miUsuario->fk_compañia);
        }
        return $consulta;
    }

    function filtrar3(Request $request, $consulta, $tabla = '') {
        $proyecto = $this->traitGetProyectoCabecera($request);
        $idUsuario = $this->traitGetTokenPersonal($request)->tokenable_id;
        $miUsuario = WbUsuarioProyecto::where('fk_usuario', $idUsuario)->where('fk_id_project_Company', $proyecto)->first();
        if (strlen($tabla) > 0) {
            $tabla = $tabla.".";
        }
        //verifico si en el proyecto actual mi empresa es la principal o subcontratista
        $consulta->where($tabla.'fk_id_project_company', $proyecto);
        if (!$this->traitEmpresaPorProyectoEsPrincipal($proyecto, $miUsuario->fk_compañia)) {
            $consulta->where($tabla.'fk_compania', $miUsuario->fk_compañia);
        }
        return $consulta;
    }

    function filtrar4(Request $request, $consulta, $tabla = '') {
        $proyecto = $this->traitGetProyectoCabecera($request);
        $idUsuario = $this->traitGetTokenPersonal($request)->tokenable_id;
        $miUsuario = WbUsuarioProyecto::where('fk_usuario', $idUsuario)->where('fk_id_project_Company', $proyecto)->first();
        if (strlen($tabla) > 0) {
            $tabla = $tabla.".";
        }
        //verifico si en el proyecto actual mi empresa es la principal o subcontratista
        $consulta->where($tabla.'fk_id_project_Company', $proyecto);
        if (!$this->traitEmpresaPorProyectoEsPrincipal($proyecto, $miUsuario->fk_compañia)) {
            $consulta->where($tabla.'fk_compania', $miUsuario->fk_compañia);
        }
        return $consulta;
    }

    function filtrarPorProyecto(Request $request, $consulta, $tabla = '') {
        $proyecto = $this->traitGetProyectoCabecera($request);
        if (strlen($tabla) > 0) {
            $tabla = $tabla.".";
        }
        $consulta->where($tabla.'fk_id_project_Company', $proyecto);
        return $consulta;
    }
}
