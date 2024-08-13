<?php

namespace App\Http\Middleware;

use App\Http\trait\CrytoTrait;
use App\Http\trait\PasswordTrait;
use App\Http\trait\TipoClavePasswordTrait;
use App\Models\Wb_password_hash;
use App\Models\WbTipoPasword;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class Desencriptador
{
    use TipoClavePasswordTrait,
        PasswordTrait,
        CrytoTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (strlen($this->traitGetNombreTipoClave($request)) == 0) {
            abort(403, "Contraseña no valida!!!!!!!");
        }
        $tipoPasswordConsultado = WbTipoPasword::where('nombre', '=', $this->traitGetNombreTipoClave($request))->first();
        if ($tipoPasswordConsultado == null) {
            $tipoPasswordCrear = new WbTipoPasword;
            $tipoPasswordCrear->nombre = $this->traitGetNombreTipoClave($request);
            try {
                if ($tipoPasswordCrear->save()) {
                    $tipoPasswordCrear->id = $tipoPasswordCrear->latest('id')->first()->id;
                    $tipoPaswrod = $tipoPasswordCrear;
                }
            } catch (\Exception $exc) {
                abort(403, $tipoPasswordCrear->id);
            }
        } else {
            $tipoPaswrod = $tipoPasswordConsultado;
        }
        $contrasena = Wb_password_hash::where('tipo_password', $tipoPaswrod->id)->first();
        if (!$contrasena) {
            abort(403, $tipoPaswrod->id);
        }
        if ($request->method() != 'GET' && $request->method() != 'DELETE' && $request->json()->has('data')) {
            $data = $request->json()->get('data');
            /**
             * Limpio el json request, para que no pasen datos sin encriptar
             */
            $request->setJson(new ParameterBag());
            $desencriptar = $this->decrypt($data, $contrasena->nombre);
            try {
                /**
                 * Agrego los datos desencriptado al json request
                 */
                $request->json()->add(json_decode($desencriptar, true));
            } catch (\Exception $exc) {
                abort(403, 'Contraseña no valida!!!!!!!');
            }
        } else if (!env('APP_DEBUG')) {
            $request->setJson(new ParameterBag());
        }
        $response = $next($request);
        //verifico si la contraseña es diferente null, si es null envio en la respuesta codigo 500 yu la data vacia
        if ($contrasena) {
            /**
             * Verifico que en el response exista el parametro data, si existe la encripto,
             * sobreescribo por el dato encriptado y le digo al response que el dato esta encriptado con la
             * variable encript en valor true
             */

            if (is_array($response->getOriginalContent()) && array_key_exists('data', $response->getOriginalContent())) {
                $data = $response->getOriginalContent()['data'];
                $encriptar = $this->encrypt($data, $contrasena->nombre);
                $response->getOriginalContent()['data'] = $encriptar;
                $resultado = $response->getOriginalContent();
                $resultado['data'] = $encriptar;
                $resultado['encript'] = true;
            } else {
                /**
                 * Si no existe la variable data en mi response, le asigno el valor ''
                 */
                $resultado = $response->getOriginalContent();
                $resultado['data'] = '';
                $resultado['encript'] = false;
            }
            /**
             * Le digo a mi request que reempleaze su contenido por el nuevo valor que se ha creado con la variable
             * $resultado
             */
            $response->setContent(
                json_encode($resultado)
            );
        } else {
            abort(403, "No tiene autorizacion");
        }
        return $response;
    }
}
