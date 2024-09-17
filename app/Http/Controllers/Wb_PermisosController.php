<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController as BaseController;
use App\Http\Resources\Wb_PermisosResource;
use App\Models\Wb_Seguri_Permiso;
use App\Models\Roles\WbSeguriRolesPermiso;
use App\Models\WbUsuarioProyecto;
use App\Models\Usuarios\usuarios_M;
use App\Models\Roles\WbSeguriRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Database\Query\JoinClause;


class Wb_PermisosController extends BaseController
{
    /**
     * Lista todos los permisos de la base de datos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->handleResponse($request, new Wb_PermisosResource(Wb_Seguri_Permiso::get()), 'Ok');
    }

    function get(Request $request)
    {
        $consulta = Wb_Seguri_Permiso::orderBy('nombrePermiso')
            ->where('nombrePermiso', '!=', 'R234')
            ->get();
        return $this->handleResponse($request, $this->permisoToArray($consulta), __("messages.consultado"));
    }

    public function permisosGestionProyecto(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $token = $this->traitGetTokenCabecera($req);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if ($tokenPersonal == null) {
            return $this->handleAlert('Permiso no valido.', false);
        }
        $permisos = [];
        $permisos['crearProyecto'] = false;
        $permisos['modificarProyecto'] = false;
        $permisos['crearCompany'] = false;
        $permisos['modificarCompany'] = false;
        $permisos['bloquearProyecto'] = false;
        $permisos['bloquearCompany'] = false;
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['crearProyecto'] = true;
            $permisos['modificarProyecto'] = true;
            $permisos['crearCompany'] = true;
            $permisos['modificarCompany'] = true;
            $permisos['bloquearProyecto'] = true;
            $permisos['bloquearCompany'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'CREAR_PROYECTO':
                    $permisos['crearProyecto'] = true;
                    break;
                case 'MODIFICAR_PROYECTO':
                    $permisos['modificarProyecto'] = true;
                    break;
                case 'CREAR_COMPAÑIA':
                    $permisos['crearCompany'] = true;
                    break;
                case 'MODIFICAR_COMPAÑIA':
                    $permisos['modificarCompany'] = true;
                    break;
                case 'BLOQUEAR_PROYECTO':
                    $permisos['bloquearProyecto'] = true;
                    break;
                case 'BLOQUEAR_COMPANIA':
                    $permisos['bloquearCompany'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosGestionCompania(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $token = $this->traitGetTokenCabecera($req);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if ($tokenPersonal == null) {
            return $this->handleAlert('Permiso no valido.', false);
        }
        $permisos = [];
        $permisos['crearCompany'] = false;
        $permisos['modificarCompany'] = false;
        $permisos['bloquearCompany'] = false;
        $permisos['asignarCompanyAProyecto'] = false;
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['crearCompany'] = true;
            $permisos['modificarCompany'] = true;
            $permisos['bloquearCompany'] = true;
            $permisos['asignarCompanyAProyecto'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'CREAR_COMPAÑIA':
                    $permisos['crearCompany'] = true;
                    break;
                case 'MODIFICAR_COMPAÑIA':
                    $permisos['modificarCompany'] = true;
                    break;
                case 'BLOQUEAR_COMPANIA':
                    $permisos['bloquearCompany'] = true;
                    break;
                case 'ASIGNAR_COMPANIA_A_PROYECTO':
                    $permisos['asignarCompanyAProyecto'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosCrubReporte(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $token = $this->traitGetTokenCabecera($req);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if ($tokenPersonal == null) {
            return $this->handleAlert('Permiso no valido.', false);
        }
        $permisos = [];
        $permisos['equipo'] = false;
        $permisos['conductor'] = false;
        $permisos['producto'] = false;
        $permisos['borrar'] = false;
        $permisos['origenDestino'] = false;
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['equipo'] = true;
            $permisos['conductor'] = true;
            $permisos['producto'] = true;
            $permisos['borrar'] = true;
            $permisos['origenDestino'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'SYNCR_VEH_EDI':
                    $permisos['equipo'] = true;
                    break;
                case 'SYNCR_CON_EDI':
                    $permisos['conductor'] = true;
                    break;
                case 'SYNCR_MAT_EDI':
                    $permisos['producto'] = true;
                    break;
                case 'SYNCR_DEL_VIA':
                    $permisos['borrar'] = true;
                    break;
                case 'SYNCR_FRE_EDI':
                    $permisos['origenDestino'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosParaUsuario(Request $req)
    {
        $token = $this->traitGetTokenCabecera($req);
        $proyecto = $this->traitGetProyectoCabecera($req);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if ($tokenPersonal == null) {
            return $this->handleAlert('Permiso no valido.', false);
        }
        $permisos = [];
        $permisos['registrar'] = false;
        $permisos['modificar'] = false;
        $permisos['inhabilitar'] = false;
        $permisos['bloquear'] = false;
        $permisos['asignarProyecto'] = false;
        $permisos['desasignarProyecto'] = false;
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['registrar'] = true;
            $permisos['modificar'] = true;
            $permisos['inhabilitar'] = true;
            $permisos['bloquear'] = true;
            $permisos['asignarProyecto'] = true;
            $permisos['desasignarProyecto'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitPermisosPorRol($miProyecto->fk_rol);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'CREAR_USUARIO':
                    $permisos['registrar'] = true;
                    break;
                case 'BLOQ_USUARIOS':
                    $permisos['bloquear'] = true;
                    break;
                case 'MODIFICAR_USUARIO':
                    $permisos['modificar'] = true;
                    break;
                case 'INHABILITAR_USUARIO':
                    $permisos['inhabilitar'] = true;
                    break;
                case 'ASIGNAR_USUARIO_PROYECTO':
                    $permisos['asignarProyecto'] = true;
                    break;
                case 'DESASIGNAR_USUARIO_PROYECTO':
                    $permisos['desasignarProyecto'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosSolicitudConcreto(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $permisos = [];
        $permisos['despachar'] = false;
        $permisos['anular_viajes'] = false;
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['despachar'] = true;
            $permisos['anular_viajes'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'DESPACHAR':
                    $permisos['despachar'] = true;
                    break;
                case 'ANULAR_VIAJES':
                    $permisos['anular_viajes'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosEquipos(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisos = [];
        $permisos['vehCon'] = false;
        $permisos['vehAdd'] = false;
        $permisos['vehEdi'] = false;
        $permisos['isAdmin'] = false;
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['vehCon'] = true;
            $permisos['vehAdd'] = true;
            $permisos['vehEdi'] = true;
            $permisos['isAdmin'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'SYNC_VEH_CON':
                    $permisos['vehCon'] = true;
                    break;
                case 'SYNC_VEH_ADD':
                    $permisos['vehAdd'] = true;
                    break;
                case 'SYNC_VEH_EDI':
                    $permisos['vehEdi'] = true;
                    break;
                case 'SYNC_IS_ADMIN':
                    $permisos['isAdmin'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosHallazgo(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisos = [];
        $permisos['verhallazgo'] = false;
        $permisos['crearHallazgo'] = false;
        $permisos['modificarHallazgo'] = false;
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['verhallazgo'] = true;
            $permisos['crearHallazgo'] = true;
            $permisos['modificarHallazgo'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'VER_HALLAZGO':
                    $permisos['verhallazgo'] = true;
                    break;
                case 'CREAR_HALLAZGO':
                    $permisos['crearHallazgo'] = true;
                    break;
                case 'MODIFICAR_HALLAZGO':
                    $permisos['modificarHallazgo'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosInformeHallazgo(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisos = [];
        $permisos['verInformehallazgo'] = false;
        $permisos['crearInformeHallazgo'] = false;
        $permisos['cambiarEstadoInformeHallazgo'] = false;
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['verInformehallazgo'] = true;
            $permisos['crearInformeHallazgo'] = true;
            $permisos['cambiarEstadoInformeHallazgo'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'VER_INFORME_DE_CAMPO':
                    $permisos['verInformehallazgo'] = true;
                    break;
                case 'CREAR_INFORME_DE_CAMPO':
                    $permisos['crearInformeHallazgo'] = true;
                    break;
                case 'CAMBIAR_ESTADO_INFORME_DE_CAMPO':
                    $permisos['cambiarEstadoInformeHallazgo'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosRutaNacional(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisos = [];
        $permisos['verRutaNacional'] = false;
        $permisos['crearRutaNacional'] = false;
        $permisos['modificarRutaNacional'] = false;
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['verRutaNacional'] = true;
            $permisos['crearRutaNacional'] = true;
            $permisos['modificarRutaNacional'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'VER_RUTA_NACIONAL':
                    $permisos['verRutaNacional'] = true;
                    break;
                case 'CREAR_RUTA_NACIONAL':
                    $permisos['crearRutaNacional'] = true;
                    break;
                case 'MODIFICAR_RUTA_NACIONAL':
                    $permisos['modificarRutaNacional'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosSolicitudAsfalto(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisos = [];
        $permisos['verSolicitudAsfalto'] = false;
        $permisos['despachar'] = false;
        $permisos['anular_viajes'] = false;
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['verSolicitudAsfalto'] = true;
            $permisos['despachar'] = true;
            $permisos['anular_viajes'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'VER_SOLI_ASFALTO':
                    $permisos['verSolicitudAsfalto'] = true;
                    break;
                case 'DESPACHAR':
                    $permisos['despachar'] = true;
                    break;
                case 'ANULAR_VIAJES':
                    $permisos['anular_viajes'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosMenuWeb(Request $req)
    {
        $token = $this->traitGetTokenCabecera($req);
        $proyecto = $this->traitGetProyectoCabecera($req);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if ($tokenPersonal == null) {
            return $this->handleAlert('Permiso no valido.', false);
        }
        $permisos = [];
        $permisos['menuUsuario'] = false;
        $permisos['menuFormulas'] = false;
        $permisos['menuActividades'] = false;
        $permisos['menuAreas'] = false;
        $permisos['menuCompanies'] = false;
        $permisos['menuUbicaciones'] = false;
        $permisos['menuSolcicitudesConcreto'] = false;
        $permisos['menuSolcicitudesAsfalto'] = false;
        $permisos['menuSolcicitudesMateriales'] = false;
        $permisos['menuReportes'] = false;
        $permisos['menuSuperliberador'] = false;
        $permisos['menuPreoperacionales'] = false;
        $permisos['menuReporteBascula'] = false;
        $permisos['menuEquipoBascula'] = false;
        $permisos['menuConductorbascula'] = false;
        $permisos['menuProyecto'] = false;
        $permisos['menuSeguridadSitio'] = false;
        $permisos['menuGestionTurnoSeguridadSitio'] = false;
        $permisos['menuGestionEnsayosLaboratorio'] = false;
        $permisos['menuGestionLaboratorio'] = false;
        $permisos['menuGestionTipoControlLaboratorio'] = false;
        $permisos['menuGestionTipoMuestreoLaboratorio'] = false;
        $permisos['menuGestionProgramadorTareas'] = false;
        $permisos['menuGestionSolicitudMuestraLaboratorio'] = false;
        $permisos['menuGestionMuestraLaboratorio'] = false;
        $permisos['menuMotivoRechazo'] = false;
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['menuUsuario'] = true;
            $permisos['menuFormulas'] = true;
            $permisos['menuActividades'] = true;
            $permisos['menuAreas'] = true;
            $permisos['menuCompanies'] = true;
            $permisos['menuUbicaciones'] = true;
            $permisos['menuSolcicitudesConcreto'] = true;
            $permisos['menuSolcicitudesAsfalto'] = true;
            $permisos['menuSolcicitudesMateriales'] = true;
            $permisos['menuReportes'] = true;
            $permisos['menuSuperliberador'] = true;
            $permisos['menuPreoperacionales'] = true;
            $permisos['menuReporteBascula'] = true;
            $permisos['menuEquipoBascula'] = true;
            $permisos['menuConductorbascula'] = true;
            $permisos['menuProyecto'] = true;
            $permisos['menuRutaNacional'] = true;
            $permisos['menuHallazgo'] = true;
            $permisos['menuInformeHallazgo'] = true;
            $permisos['menuSeguridadSitio'] = true;
            $permisos['menuGestionTurnoSeguridadSitio'] = true;
            $permisos['menuGestionEnsayosLaboratorio'] = true;
            $permisos['menuGestionLaboratorio'] = true;
            $permisos['menuGestionTipoControlLaboratorio'] = true;
            $permisos['menuGestionTipoMuestreoLaboratorio'] = true;
            $permisos['menuGestionProgramadorTareas'] = true;
            $permisos['menuGestionSolicitudMuestraLaboratorio'] = true;
            $permisos['menuGestionMuestraLaboratorio'] = true;
            $permisos['menuMotivoRechazo'] = true;
            return $this->handleResponse($req, $permisos, __("messages.consultado"));
        }
        $permisoRol = $this->traitPermisosPorRol($miProyecto->fk_rol);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'VER_MATE_ENVIADOS':
                    $permisos['menuSolcicitudesMateriales'] = true;
                    break;
                case 'VER_PREOPERACIONAL':
                    $permisos['menuPreoperacionales'] = true;
                    break;
                case 'WB_SULIBERADOR':
                    $permisos['menuSuperliberador'] = true;
                    break;
                case 'SYNC_VEH_CON':
                    $permisos['menuMaquinaria'] = true;
                    break;
                case 'SYNC_REPORTE':
                    $permisos['menuReporteBascula'] = true;
                    break;
                case 'SYNC_CON_CON':
                    $permisos['menuConductorbascula'] = true;
                    break;
                case 'VER_RESPORTES':
                    $permisos['menuReportes'] = true;
                    break;
                case 'VER_SOLI_CONCRETO':
                    $permisos['menuSolcicitudesConcreto'] = true;
                    break;
                case 'VER_SOLI_ASFALTO':
                    $permisos['menuSolcicitudesAsfalto'] = true;
                    break;
                case 'CREAR_UBICACIONES':
                    $permisos['menuUbicaciones'] = true;
                    break;
                case 'CREAR_COMPANIA':
                    $permisos['menuCompanies'] = true;
                    break;
                case 'VER_USUARIOS':
                    $permisos['menuUsuario'] = true;
                    break;
                case 'CREAR_FORMULAS':
                    $permisos['menuFormulas'] = true;
                    break;
                case 'CREAR_ACTIVIDADES':
                    $permisos['menuActividades'] = true;
                    break;
                case 'CREAR_AREAS':
                    $permisos['menuAreas'] = true;
                    break;
                case 'VER_PROYECTOS':
                    $permisos['menuProyecto'] = true;
                    break;
                case 'VER_RUTA_NACIONAL':
                    $permisos['menuRutaNacional'] = true;
                    break;
                case 'VER_HALLAZGO':
                    $permisos['menuHallazgo'] = true;
                    break;
                case 'VER_INFORME_DE_CAMPO':
                    $permisos['menuInformeHallazgo'] = true;
                    break;
                case 'VER_SEGURIDAD_SITIO':
                    $permisos['menuSeguridadSitio'] = true;
                    break;
                case 'GESTION_TURNO_SEGURIDAD_SITIO':
                    $permisos['menuGestionTurnoSeguridadSitio'] = true;
                    break;
                case 'LABORATORIO_ENSAYOS_VER':
                    $permisos['menuGestionEnsayosLaboratorio'] = true;
                    break;
                case 'LABORATORIO_VER':
                    $permisos['menuGestionLaboratorio'] = true;
                    break;
                case 'LABORATORIO_TIPO_CONTROL_VER':
                    $permisos['menuGestionTipoControlLaboratorio'] = true;
                    break;
                case 'LABORATORIO_TIPO_MUESTREO_VER':
                    $permisos['menuGestionTipoMuestreoLaboratorio'] = true;
                    break;
                case 'PROGRAMADOR_TAREAS_VER':
                    $permisos['menuGestionProgramadorTareas'] = true;
                    break;
                case 'LABORATORIO_SOLICITUD_MUESTRA_VER':
                    $permisos['menuGestionSolicitudMuestraLaboratorio'] = true;
                    break;
                case 'LABORATORIO_MUESTRA_GESTION_VER':
                    $permisos['menuGestionMuestraLaboratorio'] = true;
                    break;
                case 'MOTIVO_RECHAZO_VER':
                    $permisos['menuMotivoRechazo'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __("messages.consultado"));
    }

    public function permisosSyncEmpleado(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $token = $this->traitGetTokenCabecera($req);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if ($tokenPersonal == null) {
            return $this->handleAlert('Permiso no valido.', false);
        }
        $permisos = [];
        $permisos['consultar'] = false;
        $permisos['agregar'] = false;
        $permisos['editar'] = false;
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            $permisos['consultar'] = true;
            $permisos['agregar'] = true;
            $permisos['editar'] = true;
            return $this->handleResponse($req, $permisos, __('messages.consultado'));
        }
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        foreach ($permisoRol as $permiso) {
            switch ($permiso->nombre) {
                case 'BAS_CON':
                    $permisos['consultar'] = true;
                    break;
                case 'BAS_ADD':
                    $permisos['agregar'] = true;
                    break;
                case 'BAS_EDI':
                    $permisos['editar'] = true;
                    break;
            }
        }
        return $this->handleResponse($req, $permisos, __('messages.consultado'));
    }

    public function estoyAutorizado(Request $req, $permiso)
    {
        $token = $this->traitGetTokenCabecera($req);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if ($tokenPersonal == null) {
            return $this->handleAlert('Permiso no valido.');
        }
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($req);
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $miProyecto->fk_rol);
        if ($permisoRol->count() > 0) {
            return $this->handleAlert([], true);
        }
        $permisoRol = $this->traitGetPermisosPorNombrePermisoYRolActivo($permiso, $miProyecto->fk_rol);
        return $this->handleAlert([], $permisoRol->count() > 0);
    }

    public function getPermisosPorUsuarioLogueado(Request $req)
    {
        $proyecto = $this->traitGetProyectoCabecera($req);
        $usuario = $this->traitGetIdUsuarioToken($req);
        $permisoRol = $this->traitGetPermisosPorusuarioActivo($proyecto, $usuario);
        if ($permisoRol->count() > 0) {
            return $this->handleResponse($req, $permisoRol, 'Permiso encontrado');
        } else {
            return $this->handleAlert('Permiso no valido.', false);
        }
    }

    public function getByRol(Request $request, $permiso)
    {
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($request);
        $permisoRol = WbUsuarioProyecto::select('Wb_Seguri_Permisos.*')
            ->leftJoin('Wb_Seguri_Roles_Permisos', 'Wb_Seguri_Roles_Permisos.fk_id_Rol', 'Wb_Usuario_Proyecto.fk_rol')
            ->leftjoin('Wb_Seguri_Permisos', 'Wb_Seguri_Permisos.id_permiso', '=', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
            ->where('Wb_Usuario_Proyecto.id', $miProyecto->id)
            ->where('Wb_Seguri_Permisos.nombrePermiso', $permiso)->get();
        if ($permisoRol->count() > 0) {
            return $this->handleResponse($request, [], 'Permiso encontrado');
        } else {
            return $this->handleAlert('Permiso no valido.', false);
        }
    }
    public function getPermisosPorRol(Request $request, $rol)
    {
        $permisosPorRol = Wb_Seguri_Permiso::select('Wb_Seguri_Permisos.*')
            ->leftJoin('Wb_Seguri_Roles_Permisos', 'Wb_Seguri_Roles_Permisos.fk_id_permiso', '=', 'Wb_Seguri_Permisos.id_permiso')
            ->where('Wb_Seguri_Roles_Permisos.fk_id_Rol', $rol)
            ->get();
        return $this->handleResponse($request, $this->permisoToArray($permisosPorRol), __("messages.consultado"));
    }

    /**
     * Inserta un nuevo permiso.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'PERMISO' => 'required|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return $this->handleAlert($validator->errors(), false);
            }

            $Permiso = new Wb_Seguri_Permiso;
            $Permiso->nombrePermiso = $request['PERMISO'];
            $Permiso->save();

            return $this->handleAlert('Permiso Registrado con Exito', true);
        } catch (\Exception $e) {
            return $this->handleError('Error al insertar permiso', $e->getMessage());
        }
    }

    /**
     * Muestra los permisos asignado a un Rol.
     *
     * @param  \App\Models\Wb_Seguri_Permiso  $wb_Permisos_M
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $rol)
    {
        if (is_numeric($rol)) {
            try {
                $consulta = Wb_Seguri_Permiso::join('Wb_Seguri_Roles_Permisos', 'Wb_Seguri_Permisos.id_permiso', '=', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
                    ->join('Wb_Seguri_Roles', 'Wb_Seguri_Roles_Permisos.fk_id_Rol', '=', 'Wb_Seguri_Roles.id_Rol')
                    ->where('Wb_Seguri_Roles_Permisos.fk_id_Rol', '=', $rol)
                    ->select('id_roles_permisos AS id_permiso', 'nombrePermiso', 'nombreRol')
                    ->get();
                return $this->handleResponse($request, $this->permisoToArray($consulta), 'Ok');
            } catch (\Exception $e) {
                return $this->handleAlert('Rol no encontrado', false);
            }
        } else {
            return $this->handleAlert('Rol no encontrado', false);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wb_Seguri_Permiso $wb_Permisos_M)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Wb_Seguri_Permiso  $wb_Permisos_M
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (is_numeric($id)) {
            try {
                DB::connection('sqlsrv2')->table('Wb_Seguri_Roles_Permisos')->where('fk_id_permiso', '=', $id)->delete();
                Wb_Seguri_Permiso::destroy($id);
                return $this->handleAlert('Permiso Eliminado', true);
            } catch (\Exception $e) {
                return $this->handleAlert('Permiso no encontrado', false);
            }
        } else {
            return $this->handleAlert('Permiso no encontrado', false);
        }
    }

    //asigna un permiso a un rol existente

    public function assign(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'PERMISO' => 'required',
                'ROL' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->handleAlert('Datos Erroneos');
            }
            if (WbSeguriRolesPermiso::where('fk_id_Rol', $request->ROL)->where('fk_id_permiso', $request->PERMISO)->first() !== null) {
                return $this->handleAlert('Este permiso ya se encuentra asignado al rol.');
            }

            DB::connection('sqlsrv2')->table('Wb_Seguri_Roles_Permisos')->insert([
                'fk_id_Rol' => $request['ROL'],
                'fk_id_permiso' => $request['PERMISO']
            ]);

            return $this->handleAlert('Permiso asignado con Exito', true);
        } catch (\Exception $e) {
            return $this->handleAlert('Datos Erroneos 2', false);
            //return $this->handleError('Error al retirar permiso',$e->getMessage());
        }
    }

    //retira un permiso asignado a un rol

    public function unassign(Request $request, $permiso)
    {
        try {
            if (!is_numeric($permiso)) {
                return $this->handleAlert('Datos Erroneos 4', false);
            }
            $RolPermiso = WbSeguriRolesPermiso::find($permiso);
            if ($RolPermiso != null) {
                $RolPermiso->delete();
                return $this->handleAlert('Permiso retirado con Exito', true);
            } else {
                return $this->handleAlert('Datos Erroneos', false);
            }
        } catch (\Exception $e) {
            return $this->handleAlert('Datos Erroneos 2', false);
        }
    }

    //consulta los permisos asignados a un usuario para acceder a WEBUBASCULAS
    public function Basculasbyuser(Request $req, $user)
    {
        //valida si el usuario existe
        $usuario = usuarios_M::where('estado', 'A')->find($user);
        //si no existe devuelve error
        if (!$usuario) {
            return $this->handleAlert('Error al intentar realizar la consulta', false);
        }
        //consulata los roles activos
        $roles = WbSeguriRoles::where('estado', '1');
        //si existe consulta el id del permiso
        $permiso = $this->traitPermisoPorNombre('SYNC_BASCULA');
        //consulta los roles activos que tienen este permiso
        $rolesbascula = WbSeguriRolesPermiso::select('fk_id_Rol')->joinSub($roles, 'rol', function (JoinClause $join) {
            $join->on('fk_id_Rol', '=', 'id_Rol');
        })->where('fk_id_permiso', '=', $permiso[0]->id_permiso);
        //consulta los permisos asignados al usuario por proyecto
        $permisoRol = WbUsuarioProyecto::select('Wb_Seguri_Permisos.id_permiso as idPermiso', 'Wb_Seguri_Permisos.nombrePermiso as nombre', 'Wb_Usuario_Proyecto.fk_usuario as idUser', 'Wb_Usuario_Proyecto.fk_compañia as empresa', 'Wb_Usuario_Proyecto.fk_id_project_Company as proyecto')
            ->leftJoin('Wb_Seguri_Roles_Permisos', 'Wb_Seguri_Roles_Permisos.fk_id_Rol', 'Wb_Usuario_Proyecto.fk_rol')
            ->leftjoin('Wb_Seguri_Permisos', 'Wb_Seguri_Permisos.id_permiso', '=', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
            ->where('Wb_Usuario_Proyecto.fk_usuario', $user)
            ->whereIn('Wb_Seguri_Roles_Permisos.fk_id_Rol', $rolesbascula)->get();
        //si no encontro informacion devuelve alerta
        if ($permisoRol->count() == 0) {
            return $this->handleAlert('No permitido', false);
        }
        //devuelve los permisos
        return $this->handleResponse($req, $permisoRol, 'Permitido');
    }
    //consulta los permisos asignados a los usuarios para acceder a WEBUBASCULAS
    public function Basculas(Request $req)
    {
        //consulata los roles activos
        $roles = WbSeguriRoles::where('estado', '1');
        //si existe consulta el id del permiso
        $permiso = $this->traitPermisoPorNombre('SYNC_BASCULA');
        //consulta los roles activos que tienen este permiso
        $rolesbascula = WbSeguriRolesPermiso::select('fk_id_Rol')->joinSub($roles, 'rol', function (JoinClause $join) {
            $join->on('fk_id_Rol', '=', 'id_Rol');
        })->where('fk_id_permiso', '=', $permiso[0]->id_permiso);
        $permisoRol = WbUsuarioProyecto::select('Wb_Seguri_Permisos.id_permiso as idPermiso', 'Wb_Seguri_Permisos.nombrePermiso as nombre', 'Wb_Usuario_Proyecto.fk_usuario as idUser', 'Wb_Usuario_Proyecto.fk_compañia as empresa', 'Wb_Usuario_Proyecto.fk_id_project_Company as proyecto')
            ->leftJoin('Wb_Seguri_Roles_Permisos', 'Wb_Seguri_Roles_Permisos.fk_id_Rol', 'Wb_Usuario_Proyecto.fk_rol')
            ->leftjoin('Wb_Seguri_Permisos', 'Wb_Seguri_Permisos.id_permiso', '=', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
            ->whereIn('Wb_Seguri_Roles_Permisos.fk_id_Rol', $rolesbascula)->get();

        if ($permisoRol->count() == 0) {
            return $this->handleAlert('No se encontro informacion', false);
        }
        return $this->handleResponse($req, $permisoRol, 'Información encontrada');
    }
}
