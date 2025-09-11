<?php

namespace App\Http\trait;

use App\Models\Usuarios\WbUsuarioProyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Storage;

trait UsuarioTrait
{
    use TokenHelpersTrait;
    /**
     * @param $proyecto
     * @param $compania
     * @return array
     * Mi empresa default pertenece al proyecto actual y es principal del proyecto
     */
    public function returnResponseAutchApp($user) {
        /**
         * Consulto los proyectos del usuario
         */
        $proyectos = WbUsuarioProyecto::select(
            'Global_Project_Company.id_Project_Company as identificador',
            'Global_Project_Company.Nombre as nombre',
            'nombreCompañia as nombre_compania',
            DB::raw('CASE WHEN sub_area.id_area IS NOT NULL  THEN sub_area.id_area ELSE Area.id_area END as idArea'),
            DB::raw('CASE WHEN sub_area.Area IS NOT NULL  THEN sub_area.Area ELSE Area.Area END as nombreArea'),
            //'Area.id_area as idArea',
            //'Area.Area as nombreArea',
            'compañia.logo'
        )
            ->leftJoin('Global_Project_Company', 'Global_Project_Company.id_Project_Company', 'Wb_Usuario_Proyecto.fk_id_project_Company')
            ->leftJoin('compañia', 'compañia.id_compañia', 'Wb_Usuario_Proyecto.fk_compañia')
            ->leftJoin('Area', 'Area.id_area', 'Wb_Usuario_Proyecto.fk_area')
            ->leftJoin('Area as sub_area', 'Area.id_area', 'Wb_Usuario_Proyecto.fk_id_sub_area')
            ->where('Wb_Usuario_Proyecto.fk_usuario', $user->id_usuarios)
            ->where('Global_Project_Company.Estado', 'A')
            ->where('Wb_Usuario_Proyecto.eliminado', '0')
            ->get();
        foreach ($proyectos as $proyecto) {
            if (strlen($proyecto->logo) > 0) {
                try {
                    $patch = Storage::disk('imagenes_externas')->get($proyecto->logo);
                    //$imagedata = file_get_contents($patch);
                    $proyecto->logo = "data:image/png;base64,".base64_encode($patch);
                } catch (\Exception $exc){
                    $proyecto->logo = '';
                }
            }
        }
        /**
         * Consulta los permisos PREOPERACIONAL_OFFLINE, USE_OFFLINE por proyecto
         */
        $permisosOffline = WbUsuarioProyecto::select('Global_Project_Company.id_Project_Company as proyecto', 'Wb_Seguri_Permisos.nombrePermiso as permiso')
            ->leftJoin('Global_Project_Company', 'Global_Project_Company.id_Project_Company', 'Wb_Usuario_Proyecto.fk_id_project_Company')
            ->leftJoin('compañia', 'compañia.id_compañia', 'Wb_Usuario_Proyecto.fk_compañia')
            ->leftJoin('Wb_Seguri_Roles_Permisos', 'Wb_Seguri_Roles_Permisos.fk_id_Rol', 'Wb_Usuario_Proyecto.fk_rol')
            ->leftJoin('Wb_Seguri_Permisos', 'Wb_Seguri_Permisos.id_permiso', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
            ->where('Wb_Usuario_Proyecto.fk_usuario', $user->id_usuarios)
            ->where('Global_Project_Company.Estado', 'A')
            ->where('Wb_Usuario_Proyecto.eliminado', '0')
            ->get();
        return [
            'proyectos' => $proyectos,
            'permisos' => $permisosOffline,
            'idUsuario' => $user->id_usuarios,
            'usuario' => $user->usuario,
            'nombre' => $user->Nombre,
            'apellido' => $user->Apellido,
            'correo' => $user->Correo,
            'cedula' => $user->cedula,
            'firma' => $user->Firma,
            'area' => $user->area,
        ];
    }
}
