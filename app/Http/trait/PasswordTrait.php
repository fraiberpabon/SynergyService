<?php

namespace App\Http\trait;

use App\Models\Usuarios\Wb_password_hash;
use App\Models\WbTipoPasword;
use Illuminate\Http\Request;

trait PasswordTrait
{
    use TokenHelpersTrait, TipoClavePasswordTrait;

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPasswordActualTrait(Request $request) {
        $clave = $this->traitGetTipoClave($this->traitGetNombreTipoClave($request));
        if ($clave) {
            $contrasena = Wb_password_hash::where('tipo_password', $clave->id)->first();
            return $contrasena;
        } else {
            return null;
        }
    }

    public function eliminarPasswordTrait($contrasena) {
        try {
            if ($contrasena != null) {
                $tipoContraseña = WbTipoPasword::find($contrasena->tipo_password);
                $contrasena->delete();
                if ($tipoContraseña != null) {
                    $tipoContraseña->delete();
                }
            }
        } catch (\Exception $exc){}
    }
}
