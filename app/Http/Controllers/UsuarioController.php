<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\Area;
use App\Models\CnfCostCenter;
use App\Models\Compania;
use App\Models\Usuarios\usuarios_M;
use App\Models\UsuPlanta;
use App\Models\Roles\WbSeguriRoles;
use App\Models\Roles\WbSeguriRolesPermiso;
use App\Models\Usuarios\WbUsuarioProyecto;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class UsuarioController extends BaseController implements Vervos
{
    public function insert(Request $req)
    {
        $token = $this->traitGetTokenCabecera($req);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if ($tokenPersonal != null) {
            return $this->registrarUsuario($req);
        } else {
            return $this->registrarUsuarioExterno($req);
        }
    }

    private function registrarUsuarioExterno(Request $req)
    {
        // validar datos
        if (!$req->json()->has('password')) {
            return $this->handleAlert('Campo password no encontrado.');
        }
        if (!$req->json()->has('cedula')) {
            return $this->handleAlert('Campo cedula no encontrado.');
        }
        if (!$req->json()->has('matricula')) {
            return $this->handleAlert('Campo matricula no encontrado.');
        }
        if (!$req->json()->has('area')) {
            return $this->handleAlert('Campo area no encontrado.');
        }
        if (!$req->json()->has('rol')) {
            return $this->handleAlert('Campo fk_rol no encontrado.');
        }
        /*if(!$req->json()->has('compania')) {
            return $this->handleAlert('Campo fk_compania no encontrado.);
        }*/
        if (!$req->json()->has('nombres')) {
            return $this->handleAlert('Campo nombres no encontrado.');
        }
        if (!$req->json()->has('apellido')) {
            return $this->handleAlert('Campo apellidos no encontrado.');
        }
        if (!$req->json()->has('correo')) {
            return $this->handleAlert('Campo correo no encontrado.');
        }
        if (!$req->json()->has('celular')) {
            return $this->handleAlert('Campo celular no encontrado.');
        }
        $validator = Validator::make($req->all(), [
            'password' => 'required',
            'cedula' => 'required',
            'matricula' => 'required',
            'area' => 'required',
            'proyecto' => 'required',
            'compania' => 'required',
            'nombres' => 'required',
            'apellido' => 'required',
            'correo' => 'required',
            'celular' => 'required',
        ]);
        if (!$this->traitExisteProyectoYActivo($req->proyecto)) {
            return $this->handleAlert('Proyecto no encontrado.');
        }
        if (!$this->traitExisteEmpresaEnProyecto($req->proyecto, $req->compania)) {
            return $this->handleAlert('La empresa selecionada no pertenece al proyecto actual.');
        }
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors(), false);
        }
        $usuarioConsultado = usuarios_M::where('cedula', $req->cedula)->get();
        if ($usuarioConsultado->count() > 0) {
            return $this->handleAlert('La cedula ingresada ya se encuentra registrada.', false);
        }
        if (WbSeguriRoles::find($req->rol) == null) {
            return $this->handleAlert('Rol no encontrado.', false);
        }
        $nombreSplit = explode(' ', strtolower($req->nombres));
        $apellidosSplit = explode(' ', strtolower($req->apellido));
        $nombreAux = strtolower($req->nombres);
        if (count($nombreSplit) < 2) {
            $nombreUsuarioGenerado = substr($nombreSplit[0], 0, 2) . '.' . $apellidosSplit[0];
            $usuarioEncontrado = usuarios_M::where('usuario', $nombreUsuarioGenerado)->get();
            if ($usuarioEncontrado->count() > 0) {
                $cont = 1;
                $encontrado = true;
                while ($encontrado && $cont < strlen($apellidosSplit[1])) {
                    $nombreUsuarioGenerado = substr($nombreAux, 0, 2)
                        . '.' . $apellidosSplit[0] . substr($apellidosSplit[1], 0, $cont);
                    $usuarioEncontrado = usuarios_M::where('usuario', $nombreUsuarioGenerado)->get();
                    if ($usuarioEncontrado->count() > 0) {
                        ++$cont;
                    } else {
                        $encontrado = false;
                    }
                }
            }
        } else {
            $nombreUsuarioGenerado = substr($nombreSplit[0], 0, 1) . substr($nombreSplit[1], 0, 1) . '.' . $apellidosSplit[0];
            $usuarioEncontrado = usuarios_M::where('usuario', $nombreUsuarioGenerado)->get();
            if ($usuarioEncontrado->count() > 0) {
                $cont = 1;
                $valido = false;
                while ($valido && $cont < strlen($apellidosSplit[1])) {
                    $nombreUsuarioGenerado = substr($nombreAux, 0, 1) . substr($nombreSplit[1], 0, 1)
                        . '.' . $apellidosSplit[0] . substr($apellidosSplit[1], 0, $cont);
                    $usuarioEncontrado = usuarios_M::where('usuario', $nombreUsuarioGenerado)->get();
                    if ($usuarioEncontrado->count() > 0) {
                        ++$cont;
                    } else {
                        $valido = true;
                    }
                }
            }
        }

        $contrasena = password_hash($req->password, PASSWORD_DEFAULT, ['cost' => 10]);
        $usuarioRegistrar = new usuarios_M();
        $usuarioRegistrar->usuario = $nombreUsuarioGenerado;
        $usuarioRegistrar->contraseña = $contrasena;
        $usuarioRegistrar->cedula = $req->cedula;
        $usuarioRegistrar->matricula = $req->matricula;
        $usuarioRegistrar->area = $req->area;
        $usuarioRegistrar->estado = 'D';
        $usuarioRegistrar->fk_rol = $req->rol;
        $usuarioRegistrar->imeil = $req->imeil;
        $usuarioRegistrar->fk_compañia = $req->compania;
        $usuarioRegistrar->fk_id_project_Company = $req->proyecto;
        $usuarioRegistrar->fk_Planta_asignada = 0;
        $usuarioRegistrar->Nombre = $req->nombres;
        $usuarioRegistrar->Apellido = $req->apellido;
        $usuarioRegistrar->Firma = '';
        $usuarioRegistrar->Correo = $req->email;
        $usuarioRegistrar->celular = $req->celular;
        try {
            $usuarioRegistrar->save();
            $usuarioRegistrar->id_usuarios = $usuarioRegistrar->latest('id_usuarios')->first()->id_usuarios;
            $usuarioProyecto = new WbUsuarioProyecto();
            $usuarioProyecto->fk_usuario = $usuarioRegistrar->id_usuarios;
            $usuarioProyecto->fk_id_project_Company = $req->proyecto;
            $usuarioProyecto->fk_compañia = $req->compania;
            $usuarioProyecto->fk_rol = $req->rol;
            $usuarioProyecto->save();

            return $this->handleResponse($req, ['nombre' => $nombreUsuarioGenerado], 'Usuario registrado con exito.');
        } catch (\Exception $exc) {
            \Log::error($exc->getMessage());
            return $this->handleAlert(__('messages.error_interno'));
        }
    }

    private function registrarUsuario(Request $req)
    {
        if (!$req->json()->has('nombreCuenta')) {
            return $this->handleAlert('Campo usuario no encontrado.');
        }
        if (!$req->json()->has('password')) {
            return $this->handleAlert('Campo password no encontrado.');
        }
        if (!$req->json()->has('cedula')) {
            return $this->handleAlert('Campo cedula no encontrado.');
        }
        if (!$req->json()->has('matricula')) {
            return $this->handleAlert('Campo matricula no encontrado.');
        }
        if (!$req->json()->has('area')) {
            return $this->handleAlert('Campo area no encontrado.');
        }
        if (!$req->json()->has('rol')) {
            return $this->handleAlert('Campo fk_rol no encontrado.');
        }
        if (!$req->json()->has('proyecto')) {
            return $this->handleAlert('Campo proyecto no encontrado.');
        }
        if (!$req->json()->has('compania')) {
            return $this->handleAlert('Campo fk_compania no encontrado.');
        }
        if (!$req->json()->has('nombres')) {
            return $this->handleAlert('Campo nombres no encontrado.');
        }
        if (!$req->json()->has('apellido')) {
            return $this->handleAlert('Campo apellidos no encontrado.');
        }
        if (!$req->json()->has('correo')) {
            return $this->handleAlert('Campo email no encontrado.');
        }
        if (!$req->json()->has('celular')) {
            return $this->handleAlert('Campo celular no encontrado.');
        }
        $validator = Validator::make($req->all(), [
            'nombreCuenta' => 'required',
            'password' => 'required',
            'cedula' => 'required',
            'matricula' => 'required',
            'area' => '',
            'imeil' => 'string',
            'compania' => 'required',
            'plantaAsignada' => '',
            'nombres' => 'required',
            'apellido' => 'required',
            'correo' => 'required',
            'celular' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }
        $proyecto = $this->traitGetProyectoCabecera($req);
        if (!$this->traitExisteProyectoYActivo($proyecto)) {
            return $this->handleAlert('Proyecto no valido.');
        }
        if (!$this->traitExisteEmpresaEnProyecto($req->proyecto, $req->compania)) {
            return $this->handleAlert('La empresa selecionada no pertenece al proyecto actual.');
        }
        // validar que no exista otra cuenta con el mismo numero de cedula
        $usuarioPorCedula = usuarios_M::where('cedula', '=', $req->cedula)->get();
        if ($usuarioPorCedula->count() > 0) {
            return $this->handleAlert('La cedula ingresada ya se encuentra registrada.');
        }
        $usuarioPorNombreCuenta = usuarios_M::where('usuario', '=', $req->nombreCuenta)->get();
        // validar que no exista otro usuario con el mismo nombre de cuenta
        if ($usuarioPorNombreCuenta->count() > 0) {
            return $this->handleAlert('Este nombre de usuario ya se encuentra registrada.');
        }
        // validar que la compañia exista en el sistema
        if (Compania::find($req->compania) == null) {
            return $this->handleAlert('Compañia no encontrada.');
        }
        // vaslidar que el rol escogido exista en el sistema
        if (!$this->traitGetRolPorIdyActivo($req->rol)) {
            return $this->handleAlert('Rol no valido');
        }
        $contrasena = password_hash($req->password, PASSWORD_DEFAULT, ['cost' => 10]);
        $usuarioRegistrar = new usuarios_M();
        $usuarioRegistrar->usuario = $req->nombreCuenta;
        $usuarioRegistrar->contraseña = $contrasena;
        $usuarioRegistrar->cedula = $req->cedula;
        $usuarioRegistrar->matricula = $req->matricula;
        $usuarioRegistrar->area = $req->area;
        // los usuarios registrados por un usuario logueado son automaticamente activados para iniciar sessión
        $usuarioRegistrar->estado = 'A';
        $usuarioRegistrar->fk_rol = $req->rol;
        $usuarioRegistrar->fk_Planta_asignada = $req->plantaAsignada;
        $usuarioRegistrar->imeil = $req->imeil;
        $usuarioRegistrar->fk_id_project_Company = $proyecto;
        $usuarioRegistrar->fk_compañia = $req->compania;
        $usuarioRegistrar->Nombre = $req->nombres;
        $usuarioRegistrar->Apellido = $req->apellido;
        $usuarioRegistrar->Firma = '';
        $usuarioRegistrar->Correo = $req->correo;
        $usuarioRegistrar->celular = $req->celular;
        try {
            if ($usuarioRegistrar->save()) {
                $usuarioRegistrar->id_usuarios = $usuarioRegistrar->latest('id_usuarios')->first()->id_usuarios;
                // asignar el usuario al proyecto y compañia selecionado
                $usuarioProyecto = new WbUsuarioProyecto();
                $usuarioProyecto->fk_usuario = $usuarioRegistrar->id_usuarios;
                $usuarioProyecto->fk_compañia = $req->compania;
                $usuarioProyecto->fk_rol = $req->rol;
                $usuarioProyecto->fk_id_project_Company = $usuarioRegistrar->fk_id_project_Company;
                $usuarioProyecto->save();

                return $this->handleResponse($req, [], 'Usuario registrado con exito.');
            }
        } catch (\Exception $exc) {
        }

        return $this->handleAlert('No se pudo guardar el usuario.', false);
    }

    public function actualizarContrasenaYImei(Request $request)
    {
        if (strcmp($request->contrasenaAnterior, $request->contrasenaNueva) == 0) {
            return $this->handleCod('Las contraseña anterior y nueva no deben ser iguales.', $this->userContrasenaAnteriorYNuevaIgualesError);
        }
        if (strlen($request->imei) == 0) {
            return $this->handleCod('EL imei no puede esta vacio.', $this->userContrasenaAnteriorYNuevaIgualesError);
        }
        $user = usuarios_M::where('usuario', $request->usuario)->first();
        if (!$user) {
            return $this->handleCod('Usuario o contrasena incorrecto', $this->usuarioNoExisteError);
        }
        if (!Hash::check($request->contrasenaAnterior, $user->contraseña)) {
            return $this->handleCod('Usuario o contrasena incorrecto', $this->usuarioContrasenaNoValidaError);
        }
        $hashed = Hash::make($request->contrasenaNueva, [
            'rounds' => 10,
        ]);
        $user->contraseña = $hashed;
        $user->imeil = $request->imei;
        $user->save();
        $token = $user->createToken('login_app', [])->plainTextToken;

        return $this->handleResponse($request, $this->returnResponseAutchApp($user), 'Usuario actualizado.')
            ->header('Access-Control-Expose-Headers', 'cod-autch')
            ->header('Access-Control-Expose-Headers', 'cod-autch')
            ->header('cod-autch', $token);
    }

    public function update(Request $req, $id)
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Cedula no valida.');
        }
        if (!$req->json()->has('nombreCuenta')) {
            return $this->handleAlert('Campo usuario no encontrado.');
        }
        if (!$req->json()->has('matricula')) {
            return $this->handleAlert('Campo matricula no encontrado.');
        }
        if (!$req->json()->has('area')) {
            return $this->handleAlert('Campo area no encontrado.');
        }
        if (!$req->json()->has('estado')) {
            return $this->handleAlert('Campo estado no encontrado.');
        }
        if (!$req->json()->has('rol')) {
            return $this->handleAlert('Campo fk_rol no encontrado.');
        }
        if (!$req->json()->has('compania')) {
            return $this->handleAlert('Campo fk_compania no encontrado.');
        }
        if (!$req->json()->has('plantaAsignada')) {
            return $this->handleAlert('Campo fk_Planta_asignada no encontrado.');
        }
        if (!$req->json()->has('nombres')) {
            return $this->handleAlert('Campo nombres no encontrado.');
        }
        if (!$req->json()->has('apellido')) {
            return $this->handleAlert('Campo apellidos no encontrado.');
        }
        if (!$req->json()->has('correo')) {
            return $this->handleAlert('Campo email no encontrado.');
        }
        if (!$req->json()->has('celular')) {
            return $this->handleAlert('Campo celular no encontrado.');
        }
        $validator = Validator::make($req->all(), [
            'nombreCuenta' => 'required',
            'matricula' => 'required|numeric',
            'area' => 'required',
            'estado' => 'required',
            'rol' => 'required|numeric',
            'compania' => 'required|numeric',
            'plantaAsignada' => '',
            'nombres' => 'required',
            'apellido' => 'required',
            'celular' => 'numeric|nullable',
        ]);
        if (!$validator->fails()) {
            $proyecto = $this->traitGetProyectoCabecera($req);
            $usuarios = usuarios_M::where('cedula', $req->cedula)->get();
            if ($usuarios->count() == 0) {
                return $this->handleAlert('Usuario no encontrado.', false);
            }
            $usuarioModificar = $usuarios[0];
            $usuarioModificar->usuario = $req->nombreCuenta;
            $usuarioModificar->cedula = $req->cedula;
            $usuarioModificar->matricula = $req->matricula;
            $usuarioModificar->area = $req->area;
            $usuarioModificar->estado = $req->estado;
            $usuarioModificar->fk_rol = $req->rol;
            if ($req->imeil == null) {
                $usuarioModificar->imeil = '';
            } else {
                $usuarioModificar->imeil = $req->imeil;
            }
            $usuarioModificar->fk_compañia = $req->compania;
            $usuarioModificar->fk_Planta_asignada = $req->plantaAsignada;
            $usuarioModificar->Nombre = $req->nombres;
            $usuarioModificar->Apellido = $req->apellido;
            $usuarioModificar->Correo = $req->correo;
            $usuarioModificar->celular = $req->celular;
            try {
                $usuarioModificar->save();
                $usuarioProyecto = WbUsuarioProyecto::where('fk_usuario', $usuarioModificar->id_usuarios)->where('fk_id_project_Company', $proyecto)->first();
                if ($usuarioProyecto != null) {
                    $usuarioProyecto->fk_compañia = $req->compania;
                    $usuarioProyecto->fk_rol = $req->rol;
                    $area = Area::where('Area', $req->area)->first();
                    if ($area) {
                        $usuarioProyecto->fk_area = $area->id_area;
                    } else {
                        $usuarioProyecto->fk_area = null;
                    }
                    $usuarioProyecto->save();

                    return $this->handleResponse($req, $usuarioModificar, 'Usuario actualizado.');
                }
            } catch (\Exception $exc) {
            }
        } else {
            return $this->handleAlert($validator->errors());
        }

        return $this->handleAlert('El usuario no pudo ser modificado, intente nuevamente; si el error persiste consulte al administrador.');
    }

    private function setRolById($usuario, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($usuario->fk_rol == $array[$i]->id_Rol) {
                $reescribir['identificador'] = $array[$i]->id_Rol;
                $reescribir['nombre'] = $array[$i]->nombreRol;
                $usuario->objectRol = $reescribir;
                break;
            }
        }
    }

    /**
     * @return JsonResponse
     */
    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    public function delete(Request $request, $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Usuario no valido.');
        }
        $usuarioDeshabilitar = usuarios_M::find($id);
        if ($usuarioDeshabilitar == null) {
            return $this->handleAlert('Usuario no encontrado.');
        }
        if ($usuarioDeshabilitar->habilitado == 0) {
            return $this->handleAlert([], 'El usuario se encuentra deshabilitado.');
        }
        $usuarioDeshabilitar->habilitado = 0;
        if ($usuarioDeshabilitar->save()) {
            return $this->handleResponse($request, [], 'Usuario deshabilitado.');
        }

        return $this->handleAlert('Usuario no deshabilitado.');
    }

    public function habilitar(Request $request, $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return $this->handleAlert('Usuario no valido .');
        }
        $usuarioDeshabilitar = usuarios_M::find($id);
        if ($usuarioDeshabilitar == null) {
            return $this->handleAlert('Usuario no encontrado.');
        }
        if ($usuarioDeshabilitar->habilitado == 1) {
            return $this->handleAlert([], 'El usuario se encuentra habilitado.');
        }
        $usuarioDeshabilitar->habilitado = 1;
        if ($usuarioDeshabilitar->save()) {
            return $this->handleResponse($request, [], 'Usuario habilitado.');
        }

        return $this->handleAlert('Usuario no habilitado.');
    }

    /**
     * Actualiza la firma de un usuario
     * @param Request $request
     * @return JsonResponse
     */
    public function SubirFirma(Request $request): JsonResponse
    {
        // Validar los datos de entrada los cuales los 3 campos son obligatorios
        $validator = Validator::make($request->all(), [
            'firma' => 'required',
            'proyecto' => 'required',
            'usuario' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors(), false);
        }

        \Log::info('usuario', $request->all());

        $subirFirma = usuarios_M::where('id_usuarios', $request->usuario)
            ->first();

        if (!$subirFirma) {
            return $this->handleAlert('Usuario no encontrado.', false);
        }

        $subirFirma->Firma = $request->firma;
        $subirFirma->save();

        return $this->handleResponse($request, [], 'Usuario actualizado.');
    }

    // public function enviarCodigo(Request $request)
    // {
    //     // Validar la entrada
    //     $request->validate([
    //         'numero' => 'required|regex:/^[0-9]{10}$/',
    //     ]);

    //     // Generar y guardar un código aleatorio
    //     $confirmationController = new SmsController();
    //     $id_usuarios = 60888;
    //     $tempToken = rand(100000, 999999);
    //     $mensaje = 'WEBU: su codigo de seguridad es : WB-' . $tempToken . ' Por favor introduzca este codigo en el cuadro de confirmacion.';
    //     $nota = 'Validar Token';
    //     $confirmationController->Verificar_Usuario($mensaje, $nota, $id_usuarios, $tempToken);

    //     // Guardar el token en la sesión
    //     Session::put('tempToken', $tempToken);

    //     return response()->json(['mensaje' => 'Código enviado', 'codigo' => $tempToken]);
    // }

    public function confirmarNumero(Request $request)
    {
        // Validar la entrada
        $request->validate([
            'codigo' => 'required|numeric',
        ]);

        // Validar el código proporcionado
        if ($request->codigo == Session::get('tempToken')) {
            // Borrar el token de la sesión después de usarlo
            Session::forget('tempToken');

            return response()->json(['mensaje' => 'Número confirmado']);
        } else {
            return response()->json(['mensaje' => 'Código incorrecto'], 400);
        }
    }

    public function get(Request $request)
    {
        if (!is_numeric($request->page) || !is_numeric($request->limit)) {
            return $this->handleAlert('Datos invalidos');
        }
        $consulta = usuarios_M::select(
            'usuarioss.*'
        )
            ->join('Wb_Usuario_Proyecto', 'Wb_Usuario_Proyecto.fk_usuario', '=', 'usuarioss.id_usuarios')
            ->where('Wb_Usuario_Proyecto.eliminado', 0);
        if (strlen($request->buscar) > 0) {
            $consulta = $consulta->where(function ($query) use ($request) {
                $buscar = strtolower($request->buscar);
                $query->orWhere(DB::raw('LOWER(usuarioss.Nombre)'), 'like', DB::raw("'%$buscar%'"))
                    ->orWhere(DB::raw('LOWER(usuarioss.Apellido)'), 'like', DB::raw("'%$buscar%'"))
                    ->orWhere(DB::raw('LOWER(usuarioss.Correo)'), 'like', DB::raw("'%$buscar%'"))
                    ->orWhere(DB::raw('LOWER(usuarioss.usuario)'), 'like', DB::raw("'%$buscar%'"));
                if (is_numeric($buscar)) {
                    $query->orWhere('usuarioss.id_usuarios', $buscar)
                        ->orWhere('usuarioss.cedula', $buscar);
                }
            });
        }

        $consulta = $this->filtrar($request, $consulta, 'Wb_Usuario_Proyecto');

        $limitePaginas = 1;
        $contador = clone $consulta;
        $contador = $contador->count();
        $consulta = $consulta->forPage($request->page, $request->limit)->get();
        $limitePaginas = ($contador / $request->limit);
        if ($limitePaginas <= 1) {
            $limitePaginas = $limitePaginas = 1;
        }

        /* $consulta = $this->filtrar($request, $consulta, 'Wb_Usuario_Proyecto');
        $contador = clone $consulta;
        $contador = $contador->get();
        $consulta = $consulta->forPage($request->page, $request->limit)->get();
        $limitePaginas = ($contador->count()/$request->limit) + 1; */
        $roles = WbSeguriRoles::all();
        foreach ($consulta as $usuario) {
            $usuario->firma = '';
            $this->setRolById($usuario, $roles);
        }

        return $this->handleResponse($request, $this->usuarioToArray($consulta), __('messages.consultado'), ceil($limitePaginas));
    }

    public function miUsuario(Request $request)
    {
        $consulta = usuarios_M::find($this->traitGetIdUsuarioToken($request));
        $proyectos = WbUsuarioProyecto::find($this->traitGetProyectoCabecera($request));
        if ($consulta != null) {
            $ususPlanta = UsuPlanta::where('id_plata', $consulta->fk_Planta_asignada)->first();
            if ($ususPlanta != null) {
                $consulta->objectPlanta = $this->usuPlantaToModel($ususPlanta);
                $costCenter = CnfCostCenter::where('COCEIDENTIFICATION', $ususPlanta->fk_id_centroCosto)->first();
                if ($costCenter != null) {
                    $consulta->objectCostCenter = $this->cnfCostControlToModel($costCenter);
                }
            }
            $consulta->objectUsuarioProyecto = $this->wbUsuarioProyectoToModel($proyectos);
        }

        return $this->handleResponse($request, $this->usuarioToModel($consulta), 'Consultado');
    }

    private function setProyectoById($usuario, $array)
    {
        for ($i = 0; $i < $array->count(); ++$i) {
            if ($usuario->fk_rol == $array[$i]->id_Rol) {
                $reescribir['identificador'] = $array[$i]->id_Rol;
                $reescribir['nombre'] = $array[$i]->nombreRol;
                $usuario->objectRol = $reescribir;
                break;
            }
        }
    }

    public function changueStatus($user, Request $request)
    {
        if (!is_numeric($user)) {
            return $this->handleAlert('Usuario no valido.');
        }
        $status = $request->estado;
        if (!($status == 'A' || $status == 'D')) {
            return $this->handleAlert('Estado no valido.');
        }
        $user = usuarios_M::find($user);
        $user->estado = $status;
        try {
            if ($user->save()) {
                return $this->handleResponse($request, [], $status == 'A' ? 'Usuario activado.' : 'Usuario desactivado.');
            }
        } catch (\Exception $exc) {
        }

        return $this->handleResponse($request, [], $status == 'A' ? 'Usuario no activado.' : 'Usuario no desactivado.');
    }

    public function bloquearPorImei($imei, Request $request)
    {
        $user = usuarios_M::where('imeil', $imei)->get();
        if ($user->count() == 0) {
            return $this->handleCod(__('messages.no_existe_usuario_con_este_numero_de_imei'), $this->usuarioPorImeilNoEncontradoError);
        }
        foreach ($user as $item) {
            $item->estado = 'D';
            $item->save();
        }

        return $this->handleResponse($request, [], __('messages.usuario_bloqueado'));
    }

    public function getPorProyecto(Request $request, $proyecto) {}

    public function cambiarContrasena(Request $request)
    {
        if (strcmp($request->contrasenaAnterior, $request->contrasenaNueva) == 0) {
            return $this->handleCod('Las contraseña anterior y nueva no deben ser iguales.', $this->userContrasenaAnteriorYNuevaIgualesError);
        }
        $user = usuarios_M::find($this->traitGetIdUsuarioToken($request))->first();
        if (!$user) {
            return $this->handleCod('Usuario o contrasena incorrecto', $this->usuarioNoExisteError);
        }
        if (!Hash::check($request->contrasenaAnterior, $user->contraseña)) {
            return $this->handleCod('Usuario o contrasena incorrecto', $this->usuarioContrasenaNoValidaError);
        }
        $hashed = Hash::make($request->contrasenaNueva, [
            'rounds' => 10,
        ]);
        $user->contraseña = $hashed;
        $user->save();
        $token = $user->createToken('login_app', [])->plainTextToken;

        return $this->handleResponse($request, $this->returnResponseAutchApp($user), 'Usuario logueado')
            ->header('Access-Control-Expose-Headers', 'cod-autch')
            ->header('Access-Control-Expose-Headers', 'cod-autch')
            ->header('cod-autch', $token);
    }

    public function Restablecer($id)
    {
        $text = 'Ariguani';
        $now = new \DateTime();
        $pass = $text . $now->format('Y');

        $hashed = Hash::make($pass, [
            'rounds' => 10,
        ]);

        try {
            $actividades = usuarios_M::find($id);
            $actividades->contraseña = $hashed;
            $actividades->imeil = '';
            $actividades->save();
            $actividades->refresh();
        } catch (\Throwable $th) {
            return $this->handleError('Error', $th->getMessage());
        }

        return $this->handleAlert('Contraseña Cambiada', true);
    }

    public function getUserBascula(Request $request)
    {
        // consulta los roles activos
        $roles = WbSeguriRoles::where('estado', '1');
        // si existe consulta el id del permiso
        $permiso = $this->traitPermisoPorNombre('SYNC_BASCULA');
        // consulta los roles activos que tienen este permiso
        $rolesbascula = WbSeguriRolesPermiso::select('fk_id_Rol')->joinSub($roles, 'rol', function (JoinClause $join) {
            $join->on('fk_id_Rol', '=', 'id_Rol');
        })->where('fk_id_permiso', '=', $permiso[0]->id_permiso);
        // consulto los usuarios que tienen estos roles aplicados
        $usuariosBascula = WbUsuarioProyecto::select('usuarioss.id_usuarios as identificador', 'usuarioss.usuario as nombreCuenta', 'usuarioss.cedula as cedula', 'usuarioss.Nombre as nombres', 'usuarioss.Apellido as apellido', 'usuarioss.estado as estado')
            ->leftJoin('usuarioss', 'usuarioss.id_usuarios', 'Wb_Usuario_Proyecto.fk_usuario')
            ->whereIn('Wb_Usuario_Proyecto.fk_Rol', $rolesbascula)->get();
        if ($usuariosBascula->count() == 0) {
            return $this->handleAlert('No se encontro informacion', false);
        }

        return $this->handleResponse($request, $usuariosBascula, 'Información encontrada');
    }

    /**
     * Busca y retorna un usuario por su ID.
     *
     * @param int $id el ID del usuario que se desea buscar
     *
     * @return \App\Models\usuarios_M|null el modelo del usuario encontrado o null si no se encuentra
     */
    public function find($id)
    {
        // Utiliza el método estático find del modelo para buscar el usuario por su ID
        return usuarios_M::find($id);
    }
}
