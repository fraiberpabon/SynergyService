<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class isWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $agent = new Agent();
        /**
         * Compruebo si la solicitud viene de un navegador.
         */
        if ($agent->isChrome() || $agent->isFirefox() || $agent->isEdge() || $agent->isDesktop()) {
            return $next($request);
        } else {
            return response(['message'=> 'Usuario no valido', 'cod'=> 'dp'], 401)
                ->header('Content-Type', 'text/plain');
        }
    }
}
