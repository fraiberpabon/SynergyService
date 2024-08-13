<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Http\trait\TraitGuardarImagenBase64;
use App\Models\Compania;
use App\Models\ProjectCompany;
use App\Models\WbCompanieProyecto;
use App\Models\WbPais;
use App\Models\WbSeguriRoles;
use App\Models\WbTemaInterfaz;
use App\Models\WbUsuarioProyecto;
use Illuminate\Http\Request;

class ProjectCompanyController extends BaseController implements Vervos
{
    use TraitGuardarImagenBase64;
    public function post(Request $req)
    {
        // TODO: Implement post() method.
        if(!$req->json()->has('nombre')) {
            return $this->handleAlert('Falta campo nombre.');
        }
        if(!$req->json()->has('descripcion')) {
            return $this->handleAlert('Falta campo descripcion.');
        }
        /*if(!$req->json()->has('tema')) {
            return $this->handleAlert('Falta campo tema.');
        }*/
        if(!$req->json()->has('pais')) {
            return $this->handleAlert('Falta campo pais.');
        }
        if($req->validate([
            'nombre' => '',
            'descripcion' => '',
            'tema' => '',
            'pais' => '',
            'company' => ''
        ])) {
            /*if(WbTemaInterfaz::find($req->tema) == null) {
                return $this->handleAlert('Tema no encontrado.');
            }*/
            if(WbPais::find($req->pais) == null) {
                return $this->handleAlert('Pais no encontrado');
            }
            if(Compania::find($req->company) == null) {
                return $this->handleAlert('Compañia no encontrada');
            }
            if (ProjectCompany::where('Nombre', $req->nombre)->first() != null) {
                return $this->handleAlert('Ya existe un proyecto con el mismo nombre, por favor elija otro nombre para el proyecto.');
            }
            $modeloRegistrar = new ProjectCompany;
            $modeloRegistrar->Nombre = $req->nombre;
            $modeloRegistrar->Estado = "A";
            $modeloRegistrar->descripcion = $req->descripcion;
            /*$modeloRegistrar->fk_tema_interfaz = $req->tema;*/
            $modeloRegistrar->fk_pais = $req->pais;
            try {
                if($modeloRegistrar->save()) {
                    $proyecto = $this->traitGetProyectoCabecera($req);
                    $usuario = $this->traitGetIdUsuarioToken($req);
                    $modeloRegistrar->id_Project_Company = $modeloRegistrar->latest('id_Project_Company')->first()->id_Project_Company;
                    $miUsuarioProyecto = WbUsuarioProyecto::where('fk_id_project_Company', $proyecto)->where('fk_usuario', $usuario)->first();
                    // se debe crear un rol superadministrador en este proyecto y uno de administrador

                    // se asigna el usuario al nuevo proyecto creado
                    $usuarioProyecto = new WbUsuarioProyecto;
                    $usuarioProyecto->fk_usuario = $this->traitGetIdUsuarioToken($req);
                    $usuarioProyecto->fk_id_project_Company = $modeloRegistrar->id_Project_Company;
                    $usuarioProyecto->fk_compañia = $req->company;
                    // el rol de superadministrador se asigna al usuario que registro el proyecto si este tiene el rol de superadministrador desde el proyecto que lo registro
                    $usuarioProyecto->fk_rol = $miUsuarioProyecto['fk_rol'];
                    $usuarioProyecto->save();
                    // si el usuario seleciono una compañia se asigna este compañia al proyecto
                    $companiaProyecto = new WbCompanieProyecto;
                    $companiaProyecto->fk_compañia = $req->company;
                    $companiaProyecto->fk_id_project_Company = $modeloRegistrar->id_Project_Company;
                    $companiaProyecto->compania_principal =1;
                    $companiaProyecto->save();
                    return $this->handleResponse($req, $this->projectCompanyToModel($modeloRegistrar), 'Proyecto registrado.');
                }
            } catch(\Exception $exc) { }
            return $this->handleAlert('Proyecto no registrado.');
        }
    }

    /**
     * Función pública para actualizar un proyecto y asignar una compañía
     * @param {Request} $req - Objeto de solicitud
     * @param {number} $id - Identificador del proyecto
     * @returns {Response} - Respuesta de la solicitud
     */
    public function update(Request $req, $id)
    {
        // Verificar que el identificador del proyecto sea un número
        if(!is_numeric($id)) {
            return $this->handleAlert('Proyecto no valido.');
        }

        // Verificar que el estado sea válido
        if($req->json()->has('estado')) {
            if(!($req->estado == 'A' || $req->estado == 'I')) {
                return $this->handleAlert('Estado no valido.');
            }
        }

        // Validar los campos del formulario
        if($req->validate([
            'nombre' => '',
            'descripcion' => '',
            'pais' => ''
        ])) {

            // Buscar el proyecto a modificar
            $modeloModificar = ProjectCompany::find($id);
            if($modeloModificar == null) {
                return $this->handleAlert('Proyecto no encontrado.');
            }

            // Actualizar el país si se proporcionó
            if($req->json()->has('pais')) {
                if(WbPais::find($req->pais) == null) {
                    return $this->handleAlert('Pais no encontrado.');
                }
                $modeloModificar->fk_pais = $req->pais;
            }

            // Actualizar el tema de la interfaz si se proporcionó
            if($req->json()->has('tema')) {
                if($req->json()->has('tema') == null) {
                    return $this->handleAlert('Pais no encontrado');
                }
                $modeloModificar->fk_tema_interfaz = $req->pais;
            }

            // Actualizar el nombre si se proporcionó
            if($req->json()->has('nombre')) {
                $modeloModificar->Nombre = $req->nombre;
            }

            // Actualizar la descripción si se proporcionó
            if($req->json()->has('descripcion')) {
                $modeloModificar->descripcion = $req->descripcion;
            }

            // Asignar una compañía si se proporcionó
            if($req->json()->has('company')) {
                //busco si el proyecto tiene una compañia principal
                $companyPrincipalActual = WbCompanieProyecto::where('fk_id_project_Company', $id)
                    ->where('compania_principal', 1)
                    ->first();
                //busco si la compañia esta relacionada al proyecto
                $companyAsignar = WbCompanieProyecto::where('fk_id_project_Company', $id)
                    ->where('fk_compañia', $req->company)
                    ->first();
                // Si la compañía principal no existe, crearla y asignarla al proyecto
                if (!$companyPrincipalActual) {
                    //si la compañia a asignar existe, la cambio a principal
                    if ($companyAsignar) {
                        $companyAsignar->compania_principal = 1;
                        $companyAsignar->save();
                    } else {//si la company a asignar no existe, se crea y se asigna como principal
                        $companyPrincipalActual = new WbCompanieProyecto;
                        $companyPrincipalActual->fk_compañia = $req->company;
                        $companyPrincipalActual->fk_id_project_Company = $id;
                        $companyPrincipalActual->compania_principal = 1;
                    }
                    $companyPrincipalActual = new WbCompanieProyecto;
                    $companyPrincipalActual->fk_compañia = $req->company;
                    $companyPrincipalActual->fk_id_project_Company = $id;
                    $companyPrincipalActual->compania_principal = 1;
                } else {//si la compania principal existe, la coloco como secundaria
                    //si la compania principal es diferente a la asignar, asigno la nueva compañia como principal
                    if ($companyPrincipalActual->id != $req->company) {
                        //coloco la compañia principal como seundaria
                        $companyPrincipalActual->compania_principal = 0;
                        $companyPrincipalActual->save();
                        //si la compañia a asignar existe la coloco como principal
                        if ($companyAsignar) {
                            $companyAsignar->compania_principal = 1;
                            $companyAsignar->save();
                        } else {//si la compañia a asignar no existe, la creo y la coloco como principal
                            $companyAsignar = new WbCompanieProyecto;
                            $companyAsignar->fk_compañia = $req->company;
                            $companyAsignar->fk_id_project_Company = $id;
                            $companyAsignar->compania_principal = 1;
                            $companyAsignar->save();
                        }
                    }
                }
            }
            try {
                //guardo los datos del proyecto
                if($modeloModificar->save()) {
                    return $this->handleResponse($req, $modeloModificar, 'Proyecto modificado');
                }
            } catch (\Exception $exc) {

            }
            return $this->handleAlert('Proyecto no modificado.');
        }
    }

    public function habilitar(Request $req, $id)
    {
        // Verificar que el identificador del proyecto sea un número
        if(!is_numeric($id)) {
            return $this->handleAlert('Proyecto no valido.');
        }
        // Buscar el proyecto a habilitar
        $modeloModificar = ProjectCompany::find($id);
        if($modeloModificar == null) {
            return $this->handleAlert('Proyecto no encontrado.');
        }
        $modeloModificar->Estado = 'A';
        try{
            if ($modeloModificar->save()) {
                return $this->handleResponse($req, [], 'Proyecto habilitado');
            }
        } catch (\Exception $exc) {}
        return $this->handleAlert("Proyecto no habilitado");
    }

    public function desHabilitar(Request $req, $id)
    {
        // Verificar que el identificador del proyecto sea un número
        if(!is_numeric($id)) {
            return $this->handleAlert('Proyecto no valido.');
        }
        // Buscar el proyecto a deshabilitar
        $modeloModificar = ProjectCompany::find($id);
        if($modeloModificar == null) {
            return $this->handleAlert('Proyecto no encontrado.');
        }
        $modeloModificar->Estado = 'I';
        try{
            if ($modeloModificar->save()) {
                return $this->handleResponse($req, [], 'Proyecto deshabilitado');
            }
        } catch (\Exception $exc) {}
        return $this->handleAlert("Proyecto no deshabilitado");
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function get(Request $request)
    {
        $consulta = ProjectCompany::select('Global_Project_Company.id_Project_Company', 'Global_Project_Company.id_Project_Company',
            'Global_Project_Company.Nombre',
            'Global_Project_Company.Estado',
            'Global_Project_Company.dateCreate',
            'Global_Project_Company.fk_pais',
            'Global_Project_Company.tema',
            'Global_Project_Company.descripcion');
        if ($request->estado) {
            $consulta = $consulta->where('Estado', $request->estado);
        } else {
            $consulta = $consulta->where('Estado', 'A');
        }

        //Solor mostrar las asignacion con estado no eliminado
        //$consulta = $consulta->where('Wb_Usuario_Proyecto.eliminado', 0);
        if ($request->usuario) {
            $consulta = $consulta->leftJoin('Wb_Usuario_Proyecto', 'Wb_Usuario_Proyecto.fk_id_project_Company', 'Global_Project_Company.id_Project_Company')->where('Wb_Usuario_Proyecto.fk_usuario', $request->usuario);
        }
        // agrupo la consulta para solo tener un proyecto por asociasion a Wb_Usuario_Proyecto
        $consulta = $consulta->groupBy([
            'Global_Project_Company.id_Project_Company',
            'Global_Project_Company.id_Project_Company',
            'Global_Project_Company.Nombre',
            'Global_Project_Company.Estado',
            'Global_Project_Company.dateCreate',
            'Global_Project_Company.fk_pais',
            'Global_Project_Company.tema',
            'Global_Project_Company.descripcion'
        ])->get();
        if ($this->traitGetIdUsuarioToken($request) == null) {
                return $this->handleResponse($request, $this->projectCompanyEscondidoToArray($consulta), __("messages.consultado"));
        } else {
            foreach ($consulta as $item) {
                $companiaPrincipal = Compania::select('compañia.*')
                    ->leftjoin('Wb_Companie_Proyecto', 'Wb_Companie_Proyecto.fk_compañia', '=', 'compañia.id_compañia')
                    ->where('Wb_Companie_Proyecto.fk_id_project_Company', $item['id_Project_Company'])
                    ->where('Wb_Companie_Proyecto.compania_principal', 1)
                    ->first();
                $companias = Compania::select('compañia.*')
                    ->leftjoin('Wb_Companie_Proyecto', 'Wb_Companie_Proyecto.fk_compañia', '=', 'compañia.id_compañia')
                    ->where('Wb_Companie_Proyecto.fk_id_project_Company', $item['id_Project_Company'])
                    ->get();
                if ($request->usuario) {
                    $rol = WbSeguriRoles::leftjoin('Wb_Usuario_Proyecto', 'Wb_Usuario_Proyecto.fk_rol', 'Wb_Seguri_Roles.id_Rol')
                    ->where('Wb_Usuario_Proyecto.fk_usuario',$request->usuario )
                        ->where('Wb_Usuario_Proyecto.fk_id_project_Company', $item['id_Project_Company'])
                    ->first();
                    $item->rol = $this->wbSeguriRolesToModel($rol);
                }
                $item->companias = $this->companiaToArray($companias);
                if ($companiaPrincipal) {
                    $item->companiaPrincipal = $this->companiaToModel($companiaPrincipal);
                }
            }
            return $this->handleResponse($request, $this->projectCompanyToArray($consulta), __("messages.consultado"));
        }
    }

    public function getProyectoPorusuarioYpermisoAsignarUsuario(Request $request) {
        $consulta = $this->getProyectoPorusuarioYpermiso('ASIGNAR_USUARIO_PROYECTO', $this->traitGetIdUsuarioToken($request));
        return $this->handleResponse($request, $this->projectCompanyToArray($consulta), __("messages.consultado"));
    }

    public function getProyectoPorusuarioYpermisoAsignarCompania(Request $request) {
        $consulta = $this->getProyectoPorusuarioYpermiso('ASIGNAR_COMPANIA_A_PROYECTO', $this->traitGetIdUsuarioToken($request));
        return $this->handleResponse($request, $this->projectCompanyToArray($consulta), __("messages.consultado"));
    }

    public function getProyectoPorusuarioYpermiso($permiso, $usuario) {
        $permiso=$this->traitPermisoPorNombre($permiso);
        if ($permiso->count() > 0) {
            $consulta = ProjectCompany::select('Global_Project_Company.*')
                ->leftJoin('Wb_Usuario_Proyecto as wup', 'Global_Project_Company.id_Project_Company', 'wup.fk_id_project_Company')
                ->leftJoin('Wb_Seguri_Roles_Permisos as wsrp', 'wsrp.fk_id_Rol', 'wup.fk_rol')
                ->where('wsrp.fk_id_permiso', $permiso[0]->id_permiso)
                ->where('wup.fk_usuario', $usuario)
                ->orderBy('Global_Project_Company.Nombre')
                ->get();
            return $consulta;
        } else {
            return array();
        }
    }

    public function setCompaniaById($data, $array) {
        for ($i = 0; $i < $array->count(); $i ++) {
            if($data->fk_planta == $array[$i]->id) {
                $reescribir = $this->plantaToModel($array[$i]);
                $data->objectPlanta = $reescribir;
                break;
            }
        }
    }

    public function getByUsuario(Request $request)
    {
        $idUsuario = $this->traitGetIdUsuarioToken($request);
        $consulta = ProjectCompany::select('Global_Project_Company.*')
            ->leftJoin('Wb_Usuario_Proyecto', 'Wb_Usuario_Proyecto.fk_id_project_Company', '=', 'Global_Project_Company.id_Project_Company')
            ->where('fk_usuario', $idUsuario)
            ->where('Estado', 'A')
            ->get();
        foreach ($consulta as $item) {
            //consulto la compania principal del proyecto
            $company = WbUsuarioProyecto::select('compañia.*')->leftJoin('compañia', 'compañia.id_compañia', 'Wb_Usuario_Proyecto.fk_compañia')
                ->where('Wb_Usuario_Proyecto.fk_usuario', $idUsuario)
                ->where('Wb_Usuario_Proyecto.fk_id_project_Company', $item['id_Project_Company'])
                ->first();
            $item->companyUser = $this->companiaToModel($company);
        }
        return $this->handleResponse($request, $this->projectCompanyToArray($consulta), __("messages.consultado"));
    }

    public function getById(Request $request, $id) {
        if(!is_numeric($id)) {
            return $this->handleAlert('Proyecto no valido.');
        }
        $consulta = ProjectCompany::find($id);
        if($consulta == null) {
            return $this->handleAlert('Proyecto no encontrado.');
        }
        return $this->handleResponse($request, $consulta, __("messages.consultado"));
    }



    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
