<?php

namespace App\Http\Controllers;

use App\Models\Compania;
use App\Models\ProjectCompany;
use App\Models\User;
use App\Models\Usuarios\Sy_usuarios;
use App\Models\Usuarios\usuarios_M;
use App\Models\Usuarios\Wb_password_hash;
use App\Models\Roles\WbSeguriRolesPermiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        try {
            /**
             * Obtengo la ip de la solicitud
             */
            $ip = $this->getIp();
            $consulta = Wb_password_hash::where('ip', '=', $ip)->limit(1)->get();
            if (!$consulta->isEmpty()) {
                $contraseña = $consulta[0];
                if ($contraseña->fechaBloqueo != null) {
                    $fecha = strtotime(date("Y-d-m H:i:s", time()));
                    $fecha2 = strtotime($contraseña->fechaBloqueo);
                    if ($fecha > $fecha2) { //la contraseña expiro
                        return $this->handleAlert('La contraseña expiró.');
                    }
                }
            } else { //no existe la contraseña en la base de datos
                return $this->handleAlert('La contraseña expiró2.');
            }
            /**
             * Verifico que se reciban los parametros de usuario y password
             */
            if ($request->json()->has('usuario') == null || $request->json()->has('password') == null) { //no existe la propiedad autch en el header
                return $this->handleCod(__('messages.faltan_parametros'), $this->faltanParametrosCabeceraError);
            }
            $user = usuarios_M::with('synergyUsers')->where('usuario', $request->usuario)->first();

            if (!$user || !Hash::check($request->password, $user->contraseña)) { //usuario o contraseña incorrecto
                $contraseña->intentos += 1;
                if (!$user) {
                    return $this->handleCod(__('messages.usuario_no_encontrado'), $this->usuarioNoExisteError);
                } else {
                    return $this->handleCod(__('messages.usuario_o_contrasena_incorrecta'), $this->usuarioContrasenaNoValidaError);
                }
            }
            if ($user->estado != 'A') { //usuario bloqueado
                $contraseña->intentos += 1;
                $contraseña->save();
                return $this->handleCod(__('messages.su_cuenta_se_encuentra_bloqueada'), $this->usuarioBloqueadoError);
            }
            if ($user->habilitado != 1) {
                $contraseña->intentos += 1;
                $contraseña->save();
                return $this->handleCod(__('messages.su_cuenta_se_encuentra_deshabilitada'), $this->usuarioDeshabilitadoError);
            }
            $proyectoDefault = $this->traitGetProyectoDefaultP($user->id_usuarios);
            if ($proyectoDefault['proyecto'] == 0) {
                return $this->handleCod(__('messages.no_cuenta_con_proyectos_valido'), $this->usuarioSinProyectoError);
            }
            $agent = new Agent();
            /**
             * Compruebo si la solicitud viene de un dispositivo Android.
             */
            if ($agent->isAndroidOS() && !($agent->isChrome() || $agent->isEdge() || $agent->isFirefox())) {
                return $this->loginAndroid($request, $user, $proyectoDefault);
            }
            /* else {
                return $this->loginWeb($request, $ip, $user, $proyectoDefault);
            } */
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->handleAlert(__('messages.error_servicio'));
            //return $this->handleAlert($th->getMessage());
        }
    }

    /* public function loginWeb($request, $ip, $user, $proyectoDefault)
    {
        //obtener los permisos por el proyecto selecionado como default
        $response['permisos'] = WbSeguriRolesPermiso::select('Wb_Seguri_Permisos.id_permiso', 'Wb_Seguri_Permisos.nombrePermiso', 'Wb_Seguri_Permisos.date_create')
            ->leftjoin('Wb_Seguri_Permisos', 'Wb_Seguri_Permisos.id_permiso', '=', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
            ->where('Wb_Seguri_Roles_Permisos.fk_id_Rol', '=', $proyectoDefault['rol'])
            ->get();
        $persmiso = [];
        foreach ($response['permisos'] as &$value) {
            array_push($persmiso, $value->nombrePermiso);
        }
        $token = $user->createToken('login_usuario', $persmiso)->plainTextToken;
        $encripter = new encrypt();
        $encripter->eliminarPasswordHash($ip);
        $consulta = usuarios_M::find($user->id_usuarios);
        $proyectos = WbUsuarioProyecto::where('fk_usuario', $user->id_usuarios)->where('fk_id_project_Company', $proyectoDefault['proyecto'])->first();
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
            //Log::error($proyectos->id);
        }
        return $this->handleResponse(
            $request,
            [
                'proyecto' => $proyectoDefault['proyecto'],
                'compania' => $proyectoDefault['compania'],
                'usuario' => $this->usuarioToModel($consulta),
                'cel_confirmado' => $this->usuarioToModel($consulta)
            ],
            'Inicio de sesión correcto.'

        )

            ->header('Access-Control-Expose-Headers', 'cod-autch')
            ->header('cod-autch', $token);
        //}
    } */


    //Funcion que verifica el numero de telefono
    public function confirmarNumeroTelefono(Request $request)
    {
        try {
            // Obtener el número de teléfono desde el cuerpo de la solicitud
            $phone_number = $request->phone_number;
            if (!$phone_number) {
                throw new \Exception('messages.numero_no_proporcionado_en_la_solicitud');
            }
            // Obtener el ID de usuario
            $id_usuarios = $this->traitGetIdUsuarioToken($request);

            // Realizar una consulta para obtener el usuario
            $consulta = usuarios_M::find($id_usuarios);
            if (!$consulta) {
                throw new \Exception('messages.usuario_no_encontrado');
            }
            $celular_usuario = $phone_number;

            // Obtener el ID de usuario
            $id_usuarios = $consulta->id_usuarios;
            // Enviar el mensaje SMS con el código de autenticación
            $confirmationController = new SmsController();
            $token_auth = rand(100000, 999999);
            $mensaje = 'WEBU: su codigo de seguridad es : WB-' . $token_auth . ' Por favor introduzca este codigo en el cuadro de confirmacion.';
            $nota = 'Verificar numero telefonico';

            $confirmationResult = $confirmationController->Verificar_Usuario($mensaje, $nota, $id_usuarios, $celular_usuario);

            $confirmationResultArray = $confirmationResult->getData(true);

            if ($confirmationResultArray['success'] === true) {
                return $this->handleResponse($request, $token_auth, __('messages.token_enviado'));
            } else {
                if (isset($confirmationResultArray['res']) && $confirmationResultArray['res'] === 'bad') {
                    $logMessage = 'Mensaje response: ' . json_encode($confirmationResultArray);
                    Log::info($logMessage);
                }
                return $this->handleError__('messages.error_verificar_numero');
            }
        } catch (\Exception $e) {
            // return $this->handleError($e->getMessage(),  __('messages.error_interno'));
        }
    }


    public function verificarTelefono(Request $request)
    {

        try {
            $phone_number = $request->phone_number;
            $id_usuarios = $this->traitGetIdUsuarioToken($request);
            $usuario = usuarios_M::find($id_usuarios);
            $id_usuarios = $usuario->id_usuarios;

            if (!$phone_number) {
                return $this->handleAlert('Error al enviar verificar el numero de confirmación.');
            }

            if (!$id_usuarios) {
                return $this->handleAlert('Usuario no encontrado.');
            }

            if ($id_usuarios) {
                $usuario->celular = $phone_number;
                $usuario->cel_confirmado = 1;
                $usuario->save();
                return $this->handleResponse($request, $phone_number, ('El número de celular fue verificado correctamente.'));
            }
        } catch (\Exception $e) {
            return $this->handleError($request, $e->getMessage(), __('messages.error_interno'));
        }
    }


    private function loginAndroid($request, $user, $proyectoDefault)
    {
        /**
         * Verifico que la solicitud tenga el campo proyecto
         */
        if (!$request->json()->has('proyecto')) {
            return $this->handleCod(__('messages.faltan_parametros'), $this->faltanParametrosCabeceraError);
        }

        /**
         * Verifico que la solicitud tenga el campo imeil del dispositivo
         */
        if (!$request->json()->has('imeil')) {
            return $this->handleCod(__('messages.faltan_parametros'), $this->faltanParametrosCabeceraError);
        }
        /**
         * Verifico que la solicitud tenga el campo de la version de la aplicacion WebuApp
         */
        if (!$request->json()->has('version')) {
            return $this->handleCod(__('messages.faltan_parametros'), $this->faltanParametrosCabeceraError);
        }

        //extraigo la version de la app en numero
        //$ver = (int) str_replace(".", "", $request->version);
        /**
         * Verifico que el proyecto existe
         */
        /* if ($ver < 2021) {
            if (!ProjectCompany::find($request->proyecto)) {
                return $this->handleCod(__('messages.proyecto_no_encontrado'), $this->proyectoNoEncontradoError);
            }
        } */
        if (!ProjectCompany::find($proyectoDefault['proyecto'])) {
            return $this->handleCod(__('messages.proyecto_no_encontrado'), $this->proyectoNoEncontradoError);
        }
        /**
         * Se consulta proyecto asignado por proyecto y usuario
         */
        $proyectoUsuario = $this->getProyectoPorUsuario($proyectoDefault['proyecto'], $user->id_usuarios);
        if (!$proyectoUsuario) {
            return $this->handleCod(__('messages.el_usuario_no_se_encuentra_asignado_al_proyecto'), $this->usuarioSinProyectoError);
        } else if ($proyectoUsuario->Estado !== 'A') {
            return $this->handleCod(__('messages.el_proyecto_actual_se_encunetra_bloqueado'), $this->proyectoloqueadoError);
        }


        /**
         * Verficio que el usuario exista en la tabla de usuarios synergy
         */
        if ($user->synergyUsers == null) {
            $datos = new Sy_usuarios();
            $datos->fk_wb_id_usuarios = $user->id_usuarios;
            $datos->imei = $request->imeil;
            $datos->version = $request->version;
            $datos->save();
            $datos->refresh();
        } else {

            /**
             * El usuario no tiene imei registrado
             */
            if ($user->change_pass != null && $user->change_pass == 1) {
                return $this->handleCod('Bienvenido a Synergy, por favor actualice su contraseña.', $this->usuarioSinImeiError);
            }

            /**
             * Verifico que el imei enviado sea igual al imei que el usuario tiene actualmente
             */

            // consultamos si en la tabla de sy_usuarios tenemos un imei anteriormente registrado
            $datos = Sy_usuarios::where('fk_wb_id_usuarios', $user->id_usuarios)->first();

            // si el imei existe entonces comprovamos que sea el mismo imei de su anterior inicio de sesion
            if ($datos->imei) {
                /* if (strcmp($datos->imei, $request->imeil) != 0) {
                    return $this->handleCod(__('messages.por_favor_ingrese_desde_el_dispositivo_que_se_registro'), $this->usuarioImeiIncorrectoError);
                } */
            } else {
                // si el imei no existe entonces agregamos el nuevo imei
                $datos->imei = $request->imeil;
            }

            /**
             * Actualizo la version de aplicacion que se está usando al usuario
             */
            $datos->version = $request->version;
            $datos->save();
            $datos->refresh();
        }

        $response['permisos'] = WbSeguriRolesPermiso::select('Wb_Seguri_Permisos.id_permiso', 'Wb_Seguri_Permisos.nombrePermiso', 'Wb_Seguri_Permisos.date_create')
            ->leftjoin('Wb_Seguri_Permisos', 'Wb_Seguri_Permisos.id_permiso', '=', 'Wb_Seguri_Roles_Permisos.fk_id_permiso')
            //->where('Wb_Seguri_Roles_Permisos.fk_id_Rol', '=', $proyectoUsuario->rol)
            ->get();
        $persmiso = [];
        foreach ($response['permisos'] as &$value) {
            array_push($persmiso, $value->nombrePermiso);
        }
        /**
         * Se crea un token para el usuario
         */
        $token = $user->createToken('login_app', $persmiso)->plainTextToken;
        return $this->handleResponse(
            $request,
            $this->returnResponseAutchApp($user),
            __('messages.usuario_logueado')
        )
            ->header('Access-Control-Expose-Headers', 'cod-autch')
            ->header('cod-autch', $token);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh(Request $request)
    {
        $miUsuario = usuarios_M::find($this->traitGetIdUsuarioToken($request));
        $proyectoUsuario = $this->traitGetMiUsuarioProyectoPorId($request);
        $empresa = Compania::find($proyectoUsuario->fk_compañia);
        return $this->handleResponse($request, [
            'usuario' => $this->usuarioToModel($miUsuario),
            'empresa' => $this->companiaToModel($empresa)
        ], 'autenticado');
    }
}
