<?php

namespace App\Http\Middleware;

use App\Http\trait\DateHelpersTrait;
use App\Http\trait\MisProyectosTrait;
use App\Http\trait\PermisoModelTrait;
use App\Http\trait\TokenHelpersTrait;
use App\Models\personal_access_tokens;
use App\Models\Usuarios\usuarios_M;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class TokenIsValido
{
    use TokenHelpersTrait, PermisoModelTrait, DateHelpersTrait, MisProyectosTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->traitGetTokenCabecera($request);
        $proyecto = $this->traitGetProyectoCabecera($request);
        $miProyectoId = $request->headers->get($this->PROYECTO_HEADER, null);
        //se esta accediendo de otra pagina o no se coloco el token en la cabecera con el nombre $TOKEN
        if($token == null) {
            return response(['message'=> 'Token no recibido', 'cod' => 'nt'], 401)
                ->header('Content-Type', 'text/plain');
        }
        $tokenPersonal = $this->getTokenById($token);
        //el token no se encontro en la base de datos
        if(!$tokenPersonal) {
            return response(['message'=> 'Usuario no valido2', 'cod' => 'nt'], 401)
                ->header('Content-Type', 'text/plain');
        }

        //se lleva la cuenta de las colicitudes realizadas con el token y se guarda
        $tokenPersonal->numero_solicitudes ++;
        $tokenPersonal->save();
        //se valida que el token no haya sobrepasado su tiempo de vida, si es asi se eliminara de la base de datos
        // y no se enviara al cliente en codigo 401
        $date = Carbon::parse($tokenPersonal->created_at);
        $date->addSeconds($this->TIEMPO_BLOQUEO);
        $fechaToken= $this->traitDateFormateado($date);
        $fechaActual = $this->traitGetDateTimeNow();
        $fecha = strtotime($fechaActual);
        $fecha2 = strtotime($fechaToken);
        if($fecha > $fecha2) {
            //el token a muerto
            personal_access_tokens::where('id', $tokenPersonal->id)->forcedelete();
            return response(['message'=> 'Usuario no valido', 'cod' => 'nt'], 401)
              ->header('Content-Type', 'text/plain');
        }
        //hay que actualizar los permisos, estos se haran en cada solicitud
        $user = usuarios_M::where('id_usuarios', $tokenPersonal->tokenable_id)->first();
        $miProyecto = $this->traitGetMiUsuarioProyectoPorId($request);
        if($miProyecto == null) {
            return response(['message'=> 'Usuario no valido', 'cod' => 'nt'], 401)
                ->header('Content-Type', 'text/plain');
        }
        $permisos = $this->traitPermisosPorRol($miProyecto->fk_rol);
        $persmisoArray = [];
        foreach ($permisos as &$value) {
            array_push($persmisoArray, $value->nombre);
        }
        if($tokenPersonal->fk_id_project_Company != $proyecto) {
            $tokenRefresh = $user->createToken("login_usuario", $persmisoArray)->plainTextToken;
            $tokenPersonal = $this->getTokenById($tokenRefresh);
            $tokenPersonal->fk_id_project_Company = $proyecto;
            $tokenPersonal->save();
        } else {
            //si el token ya sobrepaso el nuymero de solicitides se consulta si ya se creo un nuevo token
            if($tokenPersonal->numero_solicitudes >= $this->NUMERO_SOLICITUDES) {
                [$id, $token] = explode('|', $token, 2);
                $ultimosToken = PersonalAccessToken::
                where('tokenable_id', $tokenPersonal->tokenable_id)
                    ->where('id', '>', $id)
                    ->orderBy('id', 'desc')
                    ->get();
                $tokenELiminar = PersonalAccessToken::
                where('tokenable_id', $tokenPersonal->tokenable_id)
                    ->where('id', '<', $id)
                    ->orderBy('id', 'desc')
                    ->get();
                foreach ($tokenELiminar as $data) {
                    $date = Carbon::parse($data->created_at);
                    $date->addMinutes($this->TIEMPO_BLOQUEO);
                    $fechaToken= $this->traitDateFormateado($date);
                    $fechaActual = $this->traitGetDateTimeNow();
                    $fecha = strtotime($fechaActual);
                    $fecha2 = strtotime($fechaToken);
                    if($fecha > $fecha2) {
                        //temporalmente se bloquea el eliminar de un token debido a que se registran consultas en interbloqueo, y al no poder continuar causa fallos en el servicio - Fraiber
                        //personal_access_tokens::where('id', $data->id)->forcedelete();
                    }
                }
                if($ultimosToken->count() > 0) {
                    $tokenRefresh = 0;
                } else {
                    $tokenRefresh = $user->createToken("login_usuario", $persmisoArray)->plainTextToken;
                    $tokenPersonal = $this->getTokenById($tokenRefresh);
                    $tokenPersonal->fk_id_project_Company = $proyecto;
                    $tokenPersonal->save();
                }
            } else {
                $tokenRefresh = $token;
            }
        }
        $request->headers->set('tokenRefresh', $tokenRefresh);
        $response = $next($request);
        $response->withHeaders([
            'tokenRefresh' => $tokenRefresh,
            'varuno' => $this->TIEMPO_BLOQUEO,
            'Access-Control-Expose-Headers' => 'tokenRefresh, varuno'
        ]);
        return $response;
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {

    }

    public function can($ability)
    {
        return in_array('*', $this->abilities) ||
               array_key_exists($ability, array_flip($this->abilities));
    }

    private function getTokenById($tokenId) {
        return PersonalAccessToken::findToken($tokenId);
    }

}
