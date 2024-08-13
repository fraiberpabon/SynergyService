<?php

namespace App\Http\Middleware;

use App\Http\trait\PermisoModelTrait;
use App\Models\usuarios_M;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

class IsHabilitado
{
    use PermisoModelTrait;
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->headers->get('cod-autch', null);
        $tokenPersonal = PersonalAccessToken::findToken($token);
        if (!$tokenPersonal) {
            return response(['message'=> 'Usuario no valido', 'cod'=> 'np'], 401)
                  ->header('Content-Type', 'text/plain');
        }
        $usuario = usuarios_M::find($tokenPersonal->tokenable_id);
        if($usuario == null || $usuario->habilitado == 0 || $usuario->estado != 'A') {
            return response(['message'=> 'Usuario deshabilitado'], 401)
                ->header('Content-Type', 'text/plain');
        }
        //validar que la empresa de el usuario no este bloqueado
        return $next($request);
    }
}
