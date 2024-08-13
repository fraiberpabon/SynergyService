<?php

namespace App\Http\trait;

use App\Models\ProjectCompany;
use App\Models\Usuarios\usuarios_M;
use App\Models\Usuarios\WbUsuarioProyecto;
use Illuminate\Http\Request;

trait MisProyectosTrait
{
    use TokenHelpersTrait;
    /**
     * @param $usuario
     * @param $proyecto
     * @return array
     * Busca por usuario su proyecto que tiene asignado como default, si este se encuentra y el proyecto no esta bloqueado
     * lo devuelve, en otro caso si es null busca en los demas proyectos el primero que encuentre
     */
    public function traitGetProyectoDefault(Request $request)
    {
        $idUsuario = $this->traitGetIdUsuarioToken($request);
        return $this->traitGetProyectoDefaultP($idUsuario);
    }

    function traitGetProyectoDefaultP($idUsuario) {
        $miUsuario = usuarios_M::find($idUsuario);
        $proyectoUsuarioDefault = WbUsuarioProyecto::where('fk_usuario', $idUsuario)->where('fk_id_project_Company', $miUsuario->fk_id_project_Company)->get();
        $proyectos = $this->traitGetAllProyetos();
        $proyectoDefault = 0;
        $companiaDefault = 0;
        $rolDefault = 0;
        $proyectoDefaultEncontrado = false;
        //si mi proyecto por default esta bloqueado, busco en mi otros proyectos si tengo
        if ($proyectoUsuarioDefault != null) {
            foreach ($proyectos as $item) {
                if ($item->id_Project_Company == $miUsuario->fk_id_project_Company && $item->Estado == 'A') {
                    $proyectoDefault = $proyectoUsuarioDefault[0]->fk_id_project_Company;
                    $companiaDefault = $proyectoUsuarioDefault[0]->fk_compañia;
                    $rolDefault = $proyectoUsuarioDefault[0]->fk_rol;
                    $proyectoDefaultEncontrado = true;
                    break;
                }
            }
        }
        if (!$proyectoDefaultEncontrado) {
            $misCompañiasProyectos = $this->traitMisProyectos($idUsuario);
            foreach ($proyectos as $item) {
                foreach ($misCompañiasProyectos as $companiaProyecto) {
                    if ($item->id_Project_Company == $companiaProyecto->fk_id_project_Company && $item->Estado == 'A') {
                        $proyectoDefault = $companiaProyecto->fk_id_project_Company;
                        $companiaDefault = $companiaProyecto->fk_compañia;
                        $rolDefault = $companiaProyecto->fk_rol;
                        $proyectoDefaultEncontrado = true;
                        break;
                    }
                }
                if ($proyectoDefaultEncontrado) {
                    break;
                }
            }
        }
        return [
            'proyecto' => $proyectoDefault,
            'compania' => $companiaDefault,
            'rol' => $rolDefault,
        ];
    }

    public function traitGetMiUsuarioProyectoPorId(Request $request)
    {
        $usuario = $this->traitGetIdUsuarioToken($request);
        $proyecto = $this->traitGetProyectoCabecera($request);
        $consulta = WbUsuarioProyecto::where('fk_usuario', $usuario)->where('fk_id_project_Company', $proyecto)->first();
        return $consulta;
    }

    public function getProyectoPorUsuario($proyecto, $usuario) {
        return ProjectCompany::select('Global_Project_Company.id_Project_Company', 'Global_Project_Company.Estado', 'Wb_Usuario_Proyecto.fk_rol as rol')
            ->leftjoin('Wb_Usuario_Proyecto', 'Wb_Usuario_Proyecto.fk_id_project_Company', 'Global_Project_Company.id_Project_Company')
            ->where('Wb_Usuario_Proyecto.fk_usuario', $usuario)
            ->where('Wb_Usuario_Proyecto.fk_id_project_Company', $proyecto)
            ->first();
    }

    /**
     * @param $proyecto
     * @param $usuario
     * @return mixed|null
     * Comprueba si el usuario tiene asignado el proyecto que se recibio en la cabecera y si el proyecto esta activo
     */
    public function traitMiProyectoExiste(Request $request)
    {
        $usuario = $this->traitGetIdUsuarioToken($request);
        $proyecto = $this->traitGetProyectoCabecera($request);
        if (is_numeric($proyecto) && is_numeric($usuario)) {
            $consulta = WbUsuarioProyecto::where('fk_id_project_Company', $proyecto)
                ->where('Wb_Usuario_Proyecto.fk_usuario', $usuario)
                ->where('Global_Project_Company.Estado', 'A')
                ->leftJoin('Global_Project_Company', 'Global_Project_Company.id_Project_Company', 'Wb_Usuario_Proyecto.fk_id_project_Company')
                ->get();
            if ($consulta->count() > 0) {
                return $consulta[0];
            }
        }
        return null;
    }

    /**
     * @param $usuario
     * @return mixed
     */
    public function traitMisProyectos($usuario)
    {
        return WbUsuarioProyecto::where('fk_usuario', $usuario)->get();
    }
}
