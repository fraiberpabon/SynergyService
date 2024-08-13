<?php

namespace App\Http\Middleware;

use App\Http\trait\MisProyectosTrait;
use App\Http\trait\PermisoModelTrait;
use App\Http\trait\ProyectoTrait;
use App\Http\trait\TokenHelpersTrait;
use App\Http\trait\UsuarioTrait;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class IsPermiso
{
    use PermisoModelTrait, TokenHelpersTrait, MisProyectosTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permiso)
    {
        $token = $this->traitGetTokenRefreshCabecera($request);
        if($token == 0) {//el token no ha sido refrescado
            $token = $this->traitGetTokenCabecera($request);
        }
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if (!$tokenPersonal) {
            return response(['message'=> 'Usuario no valido', 'cod'=> 'pe1      '.$token], 401)
                  ->header('Content-Type', 'text/plain');
        }
        $usuarioProyecto = $this->traitGetMiUsuarioProyectoPorId($request);
        if ($usuarioProyecto == null) {
            return response(['message'=> 'Usuario sin permiso', 'cod'=> 'pe2'], 401)
                ->header('Content-Type', 'text/plain');
        } else {
            if($this->traitGetPermisosPorNombrePermisoYRolActivo($permiso, $usuarioProyecto->fk_rol)->count() > 0 || $this->traitGetPermisosPorNombrePermisoYRolActivo($this->permiso_super, $usuarioProyecto->fk_rol)->count() > 0) {
                return $next($request);
            } else {
                return response(['message'=> 'Usuario sin permiso', 'cod'=> 'pe2'], 401)
                    ->header('Content-Type', 'text/plain');
            }
        }

    }
}
