<?php

namespace App\Http\trait;
use App\Models\WbTipoPasword;
use Illuminate\Http\Request;


trait TipoClavePasswordTrait
{
    public function traitGetNombreTipoClave(Request $req) {
        if ($req->vrg) {
            return $req->vrg;
        }
        return '';
    }

    function traitGetTipoClave($tipoPassword) {
        if (strlen($tipoPassword) > 0) {
            $tipoPasswordConsultado = WbTipoPasword::where('nombre', '=', $tipoPassword)->first();
            if($tipoPasswordConsultado == null && $tipoPassword) {
                $tipoPasswordCrear = new WbTipoPasword;
                $tipoPasswordCrear->nombre = $tipoPassword;
                if($tipoPasswordCrear->save()) {
                    $tipoPasswordCrear->id = $tipoPasswordCrear->latest('id')->first()->id;
                    return WbTipoPasword::where('nombre', '=', $tipoPassword)->first();
                }
            } else {
                return $tipoPasswordConsultado;
            }
        } else {
            return null;
        }
    }
}
