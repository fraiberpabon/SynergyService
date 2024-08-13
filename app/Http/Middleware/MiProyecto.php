<?php

namespace App\Http\Middleware;

use App\Http\trait\MisProyectosTrait;
use Closure;
use Illuminate\Http\Request;

class MiProyecto
{

    use MisProyectosTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
        $this->traitMiProyectoExiste($request);
        if ($this->traitMiProyectoExiste($request) == null) {
            return response(['message'=> 'Usuario no valido', 'cod'=> 'pr'], 401)
                ->header('Content-Type', 'text/plain');
        }
        return $next($request);
    }
}
